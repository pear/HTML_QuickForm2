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
 * @copyright 2006-2020 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

/** Sets up includes */
require_once dirname(dirname(__DIR__)) . '/TestHelper.php';

/**
 * Unit test for superglobal-based data source
 */
class HTML_QuickForm2_DataSource_SuperGlobalTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_GET = [
            'foo' => 'some value',
            'bar' => 'o\\\'really',
            'baz' => [
                'key' => 'some other value',
                'unescape' => 'me\\\\please'
            ]
        ];

        $_POST = [
            'foo' => 'post value',
            'bar' => 'yes\\\'really',
            'baz' => [
                'key' => 'yet another value',
                'unescape' => 'or\\\\else'
            ]
        ];

        $_FILES = [
            'foo' => [
                'name'      => 'file.doc',
                'tmp_name'  => '/tmp/nothing',
                'type'      => 'text/plain',
                'size'      => 1234,
                'error'     => UPLOAD_ERR_OK
            ],
            'bar' => [
                'name'      => ['key' => 'a\\\'thing\\\'.foobar'],
                'tmp_name'  => ['key' => 'C:\\windows\\temp\\whatever'],
                'type'      => ['key' => 'application/foobar'],
                'size'      => ['key' => 4321],
                'error'     => ['key' => UPLOAD_ERR_OK]
            ],
            'baz' => [
                'name'      => [
                                'two' => ['three' => 'grimoire.txt'],
                                'escape' => ['o\'really' => '123.jpeg']
                ],
                'tmp_name'  => [
                                'two' => ['three' => '/mount/tmp/asdzxc'],
                                'escape' => ['o\'really' => 'C:\\upload\\somefile']
                ],
                'type'      => [
                                'two' => ['three' => 'text/unreadable'],
                                'escape' => ['o\'really' => 'image/pr0n']
                ],
                'size'      => [
                                'two' => ['three' => 65536],
                                'escape' => ['o\'really' => 5678]
                ],
                'error'     => [
                                'two' => ['three' => UPLOAD_ERR_OK],
                                'escape' => ['o\'really' => UPLOAD_ERR_OK]
                ]
            ]
        ];
    }

    public function testRequestMethodGet()
    {
        $ds1 = new HTML_QuickForm2_DataSource_SuperGlobal('GET', false);
        $this->assertEquals('some value', $ds1->getValue('foo'));
        $this->assertEquals('o\\\'really', $ds1->getValue('bar'));
        $this->assertEquals('me\\\\please', $ds1->getValue('baz[unescape]'));
    }

    public function testRequestMethodPost()
    {
        $ds1 = new HTML_QuickForm2_DataSource_SuperGlobal('POST', false);
        $this->assertEquals('post value', $ds1->getValue('foo'));
        $this->assertEquals('yes\\\'really', $ds1->getValue('bar'));
        $this->assertEquals('or\\\\else', $ds1->getValue('baz[unescape]'));
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
        $this->assertEquals([
            'name'      => 'file.doc',
            'tmp_name'  => '/tmp/nothing',
            'type'      => 'text/plain',
            'size'      => 1234,
            'error'     => UPLOAD_ERR_OK
        ], $ds1->getUpload('foo'));
        $this->assertEquals([
            'name'      => 'a\\\'thing\\\'.foobar',
            'tmp_name'  => 'C:\\windows\\temp\\whatever',
            'type'      => 'application/foobar',
            'size'      => 4321,
            'error'     => UPLOAD_ERR_OK
        ], $ds1->getUpload('bar[key]'));
        $this->assertEquals([
            'name'      => 'grimoire.txt',
            'tmp_name'  => '/mount/tmp/asdzxc',
            'type'      => 'text/unreadable',
            'size'      => 65536,
            'error'     => UPLOAD_ERR_OK
        ], $ds1->getUpload('baz[two][three]'));
    }

   /**
    * See PEAR bugs #8414 and #8123
    */
    public function testQuotesAndBackslashesEscaped()
    {
        $ds = new HTML_QuickForm2_DataSource_SuperGlobal('POST');
        $this->assertEquals([
            'name'      => '123.jpeg',
            'tmp_name'  => 'C:\\upload\\somefile',
            'type'      => 'image/pr0n',
            'size'      => 5678,
            'error'     => UPLOAD_ERR_OK
        ], $ds->getUpload('baz[escape][o\'really]'));
    }
}
?>
