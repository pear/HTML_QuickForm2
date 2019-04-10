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
require_once dirname(dirname(__FILE__)) . '/TestHelper.php';

/**
 * Unit test for HTML_QuickForm2_Container overloaded methods
 */
class HTML_QuickForm2_ContainerOverloadTest extends PHPUnit_Framework_TestCase
{
    public function testAddElements()
    {
        $c = new HTML_QuickForm2_ContainerImpl('cCOT1');
        $el1 = $c->addText('eCOT1', array('size' => 30), array('label' => 'Label'));
        $this->assertSame($el1, $c->getElementById('eCOT1-0'));

        $f = $c->addFieldset('fCOT1', null, array('label' => 'Fieldset'));
        $el2 = $f->addTextarea('eCOT2');
        $this->assertSame($el2, $c->getElementById('eCOT2-0'));
    }


    public function testAddElementsWithBracketsInName()
    {
        $c = new HTML_QuickForm2_ContainerImpl('cCOT0');
        $el1 = $c->addCheckbox('chCOT[]');
        $el2 = $c->addCheckbox('chCOT[]');
        $this->assertSame($el1, $c->getElementById('chCOT-0-0'));
        $this->assertSame($el2, $c->getElementById('chCOT-1-0'));
    }

    public function testAddUnknownType()
    {
        $c = new HTML_QuickForm2_ContainerImpl('cCOT2');
        try {
            $c->addUnknown('uCOT1');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertEquals("Element type 'unknown' is not known", $e->getMessage());
            return;
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }


    public function testAddElementWithUnderscoreInType()
    {
        HTML_QuickForm2_Factory::registerElement('super_box', 'HTML_QuickForm2_Element_InputCheckbox');
        $this->assertTrue(HTML_QuickForm2_Factory::isElementRegistered('super_box'));

        $c = new HTML_QuickForm2_ContainerImpl('cCOT3');
        $el1 = $c->addSuper_Box('sBox_1');
        $el2 = $c->addsuper_box('sBox_2');
        $el3 = $c->addSuper_box('sBox_3');
        $this->assertSame($el1, $c->getElementById('sBox_1-0'));
        $this->assertSame($el2, $c->getElementById('sBox_2-0'));
        $this->assertSame($el3, $c->getElementById('sBox_3-0'));

        try {
            $c->addSuper_Select('sSel_1');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertEquals("Element type 'super_select' is not known", $e->getMessage());
            return;
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }

}
?>