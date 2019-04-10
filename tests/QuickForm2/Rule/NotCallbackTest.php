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
 * Unit test for HTML_QuickForm2_Rule_NotCallback class
 */
class HTML_QuickForm2_Rule_NotCallbackTest extends PHPUnit_Framework_TestCase
{
    public function testNegatesResult()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                  'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockEl->expects($this->atLeastOnce())
               ->method('getRawValue')->will($this->returnValue('foo'));

        $inArrayOne = new HTML_QuickForm2_Rule_NotCallback($mockEl, 'an error',
                        array('callback' => 'in_array',
                              'arguments' => array(array('foo', 'bar', 'baz'))));

        $inArrayTwo = HTML_QuickForm2_Factory::createRule(
                        'notcallback', $mockEl, 'an error',
                        array('callback'  => 'in_array',
                              'arguments' => array(array('one', 'two', 'three')))
                      );

        $this->assertFalse($inArrayOne->validate());
        $this->assertTrue($inArrayTwo->validate());
    }
}
?>