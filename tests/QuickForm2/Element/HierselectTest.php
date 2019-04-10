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
 * Unit test for HTML_QuickForm2_Element_Hierselect class
 */
class HTML_QuickForm2_Element_HierselectTest extends PHPUnit_Framework_TestCase
{
    private $_primary   = array(1 => 'one', 2 => 'two');
    private $_secondary = array(
        1 => array(11 => 'one-one', 12 => 'one-two'),
        2 => array(21 => 'two-one', 22 => 'two-two')
    );

    public function testUpdateValueOnNameChange()
    {
        $form = new HTML_QuickForm2('testHierselectForm');
        $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
            'foo' => array(1, 12),
            'bar' => array(2, 21)
        )));
        $hs   = $form->addHierselect('foo')->loadOptions(array($this->_primary, $this->_secondary));
        $this->assertEquals(array(1, 12), $hs->getValue());

        $hs->setName('bar');
        $this->assertEquals(array(2, 21), $hs->getValue());
    }

    /**
     * If data source contains explicitly provided null values, those should be used
     * @link http://pear.php.net/bugs/bug.php?id=20295
     */
    public function testBug20295()
    {
        $form = new HTML_QuickForm2('bug20295');

        $hs = $form->addHierselect('hs')->loadOptions(array($this->_primary, $this->_secondary))
                ->setValue(array(1, 12));
        $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
            'hs' => null
        )));

        $this->assertNull($hs->getValue());
    }
}
?>