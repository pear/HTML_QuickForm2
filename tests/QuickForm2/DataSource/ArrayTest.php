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
 * Unit test for array-based data source
 */
class HTML_QuickForm2_DataSource_ArrayTest extends PHPUnit_Framework_TestCase
{
   /**
    * data source being tested
    * @var  HTML_QuickForm2_DataSource_Array
    */
    protected $ds;

    public function setUp()
    {
        $this->ds = new HTML_QuickForm2_DataSource_Array(array(
            'foo' => 'some value',
            'bar' => array(
                'key'     => 'some other value',
                'nullkey' => null
            ),
            'baz' => array(
                'key1' => array(
                    'key2' => 'yet another value'
                )
            ),
            'escape' => array(
                'o\'really' => 'yes',
                'oh\\no' => 'no'
            ),
            'quux' => null
        ));
    }

    public function testReturnsNullForAbsentValue()
    {
        $this->assertNull($this->ds->getValue('something'));
        $this->assertNull($this->ds->getValue('bar[missing]'));
        // http://news.php.net/php.pear.general/30752
        $this->assertNull($this->ds->getValue('foo[key]'));
    }

    public function testGetValue()
    {
        $this->assertEquals('some value', $this->ds->getValue('foo'));
        $this->assertEquals(
            array('key' => 'some other value', 'nullkey' => null),
            $this->ds->getValue('bar')
        );
        $this->assertEquals('some other value', $this->ds->getValue('bar[key]'));
        $this->assertEquals('yet another value', $this->ds->getValue('baz[key1][key2]'));
    }

   /**
    * See PEAR bugs #8414 and #8123
    */
    public function testQuotesAndBackslashesEscaped()
    {
        $this->assertEquals('yes', $this->ds->getValue('escape[o\'really]'));
        $this->assertEquals('no', $this->ds->getValue('escape[oh\\no]'));
    }

    public function testHasValue()
    {
        $this->assertTrue($this->ds->hasValue('foo'));
        $this->assertTrue($this->ds->hasValue('quux'));
        $this->assertFalse($this->ds->hasValue('something'));
        $this->assertFalse($this->ds->hasValue('foo[key]'));

        $this->assertTrue($this->ds->hasValue('bar'));
        $this->assertTrue($this->ds->hasValue('bar[key]'));
        $this->assertTrue($this->ds->hasValue('bar[nullkey]'));
        $this->assertFalse($this->ds->hasValue('bar[missing]'));
    }
}
?>
