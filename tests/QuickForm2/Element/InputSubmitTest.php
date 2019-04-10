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
 * Unit test for HTML_QuickForm2_Element_InputSubmit class
 */
class HTML_QuickForm2_Element_InputSubmitTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_POST = array(
            'foo' => 'A button clicked'
        );
    }

    public function testCannotBeFrozen()
    {
        $submit = new HTML_QuickForm2_Element_InputSubmit('foo');
        $this->assertFalse($submit->toggleFrozen(true));
        $this->assertFalse($submit->toggleFrozen());
    }

    public function testSetValueFromSubmitDataSource()
    {
        $form = new HTML_QuickForm2('submit', 'post', null, false);
        $foo = $form->appendChild(new HTML_QuickForm2_Element_InputSubmit('foo'));
        $bar = $form->appendChild(new HTML_QuickForm2_Element_InputSubmit('bar'));

        $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
            'foo' => 'Default for foo',
            'bar' => 'Default for bar'
        )));
        $this->assertEquals('A button clicked', $foo->getValue());
        $this->assertNull($bar->getValue());

        $foo->setAttribute('disabled');
        $this->assertNull($foo->getValue());
    }
}
?>
