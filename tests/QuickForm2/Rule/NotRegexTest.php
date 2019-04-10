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
 * Unit test for HTML_QuickForm2_Rule_NotRegex class
 */
class HTML_QuickForm2_Rule_NotRegexTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyFieldsAreSkipped()
    {
        $mockEmpty = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                    'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockEmpty->expects($this->once())->method('getRawValue')
                  ->will($this->returnValue(''));
        $ruleSimple = new HTML_QuickForm2_Rule_NotRegex($mockEmpty, 'an error', '/^[a-zA-Z]+$/');
        $this->assertTrue($ruleSimple->validate());

        $mockNoUpload = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
            ->setMethods(array('getValue'))
            ->getMock();
        $mockNoUpload->expects($this->once())->method('getValue')
                     ->will($this->returnValue(array(
                        'name'     => '',
                        'type'     => '',
                        'tmp_name' => '',
                        'error'    => UPLOAD_ERR_NO_FILE,
                        'size'     => 0
                     )));
        $ruleFile = new HTML_QuickForm2_Rule_NotRegex($mockNoUpload, 'an error', '/\\.(jpe?g|gif|png)$/i');
        $this->assertTrue($ruleFile->validate());
    }

    public function testNegatesResult()
    {
        $mockComment = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                      'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockComment->expects($this->once())->method('getRawValue')
                    ->will($this->returnValue('Buy some cheap VIAGRA from our online pharmacy!!!'));
        $ruleNoSpam = new HTML_QuickForm2_Rule_NotRegex($mockComment, 'an error', '/viagra/i');
        $this->assertFalse($ruleNoSpam->validate());

        $mockUpload = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
            ->setMethods(array('getValue'))
            ->getMock();
        $mockUpload->expects($this->once())->method('getValue')
                   ->will($this->returnValue(array(
                     'name'     => 'pr0n.jpg',
                     'type'     => 'image/jpeg',
                     'tmp_name' => '/tmp/foobar',
                     'error'    => UPLOAD_ERR_OK,
                     'size'     => 123456
                   )));
        $ruleNoExe = new HTML_QuickForm2_Rule_NotRegex($mockUpload, 'an error', '/\\.(exe|scr|cmd)$/i');
        $this->assertTrue($ruleNoExe->validate());
    }
}
?>