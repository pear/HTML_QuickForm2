<?php
/**
 * Unit tests for HTML_QuickForm2 package
 *
 * PHP version 5
 *
 * LICENSE
 *
 * This source file is subject to BSD 3-Clause License that is bundled
 * with this package in the file LICENSE and available at the URL
 * https://raw.githubusercontent.com/pear/HTML_QuickForm2/trunk/docs/LICENSE
 *
 * @category  HTML
 * @package   HTML_QuickForm2
 * @author    Alexey Borzov <avb@php.net>
 * @author    Bertrand Mansion <golgote@mamasam.com>
 * @copyright 2006-2019 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

/** Sets up includes */
require_once dirname(dirname(dirname(__FILE__))) . '/TestHelper.php';

/**
 * Unit test for superglobal-based data source
 */
class HTML_QuickForm2_DataSource_SuperGlobalTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_GET = array(
            'foo' => 'some value',
            'bar' => 'o\\\'really',
            'baz' => array(
                'key' => 'some other value',
                'unescape' => 'me\\\\please'
            )
        );

        $_POST = array(
            'foo' => 'post value',
            'bar' => 'yes\\\'really',
            'baz' => array(
                'key' => 'yet another value',
                'unescape' => 'or\\\\else'
            )
        );

        $_FILES = array(
            'foo' => array(
                'name'      => 'file.doc',
                'tmp_name'  => '/tmp/nothing',
                'type'      => 'text/plain',
                'size'      => 1234,
                'error'     => UPLOAD_ERR_OK
            ),
            'bar' => array(
                'name'      => array('key' => 'a\\\'thing\\\'.foobar'),
                'tmp_name'  => array('key' => 'C:\\windows\\temp\\whatever'),
                'type'      => array('key' => 'application/foobar'),
                'size'      => array('key' => 4321),
                'error'     => array('key' => UPLOAD_ERR_OK)
            ),
            'baz' => array(
                'name'      => array(
                                'two' => array('three' => 'grimoire.txt'),
                                'escape' => array('o\'really' => '123.jpeg')
                               ),
                'tmp_name'  => array(
                                'two' => array('three' => '/mount/tmp/asdzxc'),
                                'escape' => array('o\'really' => 'C:\\upload\\somefile')
                               ),
                'type'      => array(
                                'two' => array('three' => 'text/unreadable'),
                                'escape' => array('o\'really' => 'image/pr0n')
                               ),
                'size'      => array(
                                'two' => array('three' => 65536),
                                'escape' => array('o\'really' => 5678)
                               ),
                'error'     => array(
                                'two' => array('three' => UPLOAD_ERR_OK),
                                'escape' => array('o\'really' => UPLOAD_ERR_OK)
                               )
            )
        );
    }

    public function testRequestMethodGet()
    {
        $ds1 = new HTML_QuickForm2_DataSource_SuperGlobal('GET', false);
        $this->assertEquals('some value', $ds1->getValue('foo'));
        $this->assertEquals('o\\\'really', $ds1->getValue('bar'));
        $this->assertEquals('me\\\\please', $ds1->getValue('baz[unescape]'));

        $ds2 = new HTML_QuickForm2_DataSource_SuperGlobal('GET', true);
        $this->assertEquals('some value', $ds2->getValue('foo'));
        $this->assertEquals('o\'really', $ds2->getValue('bar'));
        $this->assertEquals('me\\please', $ds2->getValue('baz[unescape]'));
    }

    public function testRequestMethodPost()
    {
        $ds1 = new HTML_QuickForm2_DataSource_SuperGlobal('POST', false);
        $this->assertEquals('post value', $ds1->getValue('foo'));
        $this->assertEquals('yes\\\'really', $ds1->getValue('bar'));
        $this->assertEquals('or\\\\else', $ds1->getValue('baz[unescape]'));

        $ds2 = new HTML_QuickForm2_DataSource_SuperGlobal('POST', true);
        $this->assertEquals('post value', $ds2->getValue('foo'));
        $this->assertEquals('yes\'really', $ds2->getValue('bar'));
        $this->assertEquals('or\\else', $ds2->getValue('baz[unescape]'));
    }

    public function testGetUploadReturnsNullForAbsentValue()
    {
        $ds = new HTML_QuickForm2_DataSource_SuperGlobal('POST');
        $this->assertNull($ds->getUpload('missing'));
        $this->assertNull($ds->getUpload('bar[missing]'));
        $this->assertNull($ds->getUpload('baz[escape][missing]'));
    }

    public function testGetUpload()
    {
        $ds1 = new HTML_QuickForm2_DataSource_SuperGlobal('POST', false);
        $this->assertEquals(array(
            'name'      => 'file.doc',
            'tmp_name'  => '/tmp/nothing',
            'type'      => 'text/plain',
            'size'      => 1234,
            'error'     => UPLOAD_ERR_OK
        ), $ds1->getUpload('foo'));
        $this->assertEquals(array(
            'name'      => 'a\\\'thing\\\'.foobar',
            'tmp_name'  => 'C:\\windows\\temp\\whatever',
            'type'      => 'application/foobar',
            'size'      => 4321,
            'error'     => UPLOAD_ERR_OK
        ), $ds1->getUpload('bar[key]'));
        $this->assertEquals(array(
            'name'      => 'grimoire.txt',
            'tmp_name'  => '/mount/tmp/asdzxc',
            'type'      => 'text/unreadable',
            'size'      => 65536,
            'error'     => UPLOAD_ERR_OK
        ), $ds1->getUpload('baz[two][three]'));

        $ds2 = new HTML_QuickForm2_DataSource_SuperGlobal('POST', true);
        $this->assertEquals(array(
            'name'      => 'a\'thing\'.foobar',
            'tmp_name'  => 'C:\\windows\\temp\\whatever',
            'type'      => 'application/foobar',
            'size'      => 4321,
            'error'     => UPLOAD_ERR_OK
        ), $ds2->getUpload('bar[key]'));
    }

   /**
    * See PEAR bugs #8414 and #8123
    */
    public function testQuotesAndBackslashesEscaped()
    {
        $ds = new HTML_QuickForm2_DataSource_SuperGlobal('POST');
        $this->assertEquals(array(
            'name'      => '123.jpeg',
            'tmp_name'  => 'C:\\upload\\somefile',
            'type'      => 'image/pr0n',
            'size'      => 5678,
            'error'     => UPLOAD_ERR_OK
        ), $ds->getUpload('baz[escape][o\'really]'));
    }
}
?>
