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
 * Unit test for HTML_QuickForm2_Element_InputImage class
 */
class HTML_QuickForm2_Element_InputImageTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_POST = array(
            'foo_x' => '12',
            'foo_y' => '34',
            'bar' => array(
                'idx' => array('56', '78')
            )
        );
    }

    public function testCannotBeFrozen()
    {
        $image = new HTML_QuickForm2_Element_InputImage('foo');
        $this->assertFalse($image->toggleFrozen(true));
        $this->assertFalse($image->toggleFrozen());
    }

    public function testPhpBug745Workaround()
    {
        $image1 = new HTML_QuickForm2_Element_InputImage('foo');
        $this->assertRegExp('/name="foo"/', $image1->__toString());

        $image2 = new HTML_QuickForm2_Element_InputImage('foo[bar]');
        $this->assertRegExp('/name="foo\\[bar\\]\\[\\]"/', $image2->__toString());
        $this->assertEquals('foo[bar]', $image2->getName());

        $image3 = new HTML_QuickForm2_Element_InputImage('foo[bar][]');
        $this->assertRegExp('/name="foo\\[bar\\]\\[\\]"/', $image3->__toString());
        $this->assertEquals('foo[bar][]', $image3->getName());
    }

    public function testSetValueFromSubmitDataSource()
    {
        $form = new HTML_QuickForm2('image', 'post', null, false);
        $foo = $form->appendChild(new HTML_QuickForm2_Element_InputImage('foo'));
        $bar = $form->appendChild(new HTML_QuickForm2_Element_InputImage('bar[idx]'));

        $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
            'foo_x' => '1234',
            'foo_y' => '5678',
            'bar' => array(
                'idx' => array('98', '76')
            )
        )));
        $this->assertEquals(array('x' => '12', 'y' => '34'), $foo->getValue());
        $this->assertEquals(array('x' => '56', 'y' => '78'), $bar->getValue());

        $foo->setAttribute('disabled');
        $this->assertNull($foo->getValue());
    }
}
?>
