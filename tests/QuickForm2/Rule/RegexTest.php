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
 * Unit test for HTML_QuickForm2_Rule_Regex class
 */
class HTML_QuickForm2_Rule_RegexTest extends PHPUnit_Framework_TestCase
{
    public function testRegexIsRequired()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                 'getRawValue', 'setValue', '__toString'))
            ->getMock();
        try {
            $regex = new HTML_QuickForm2_Rule_Regex($mockEl, 'some error');
            $this->fail('Expected HTML_QuickForm2_Exception was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/Regex Rule requires a regular expression/', $e->getMessage());
            return;
        }
    }

    public function testOptionsHandling()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                 'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockEl->expects($this->exactly(2))->method('getRawValue')
               ->will($this->returnValue('foo123'));

        $alpha = new HTML_QuickForm2_Rule_Regex($mockEl, 'an error', '/^[a-zA-Z]+$/');
        $this->assertFalse($alpha->validate());

        $alphaNum = new HTML_QuickForm2_Rule_Regex($mockEl, 'an error', '/^[a-zA-Z0-9]+$/');
        $this->assertTrue($alphaNum->validate());
    }

    public function testConfigHandling()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                  'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockEl->expects($this->exactly(2))->method('getRawValue')
               ->will($this->returnValue('foo'));

        HTML_QuickForm2_Factory::registerRule('regex-alpha', 'HTML_QuickForm2_Rule_Regex',
                                              null, '/^[a-zA-Z]+$/');
        $alpha = HTML_QuickForm2_Factory::createRule('regex-alpha', $mockEl, 'an error');
        $this->assertTrue($alpha->validate());

        HTML_QuickForm2_Factory::registerRule('regex-numeric', 'HTML_QuickForm2_Rule_Regex',
                                              null, '/(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/');
        $numeric = HTML_QuickForm2_Factory::createRule('regex-numeric', $mockEl, 'an error');
        $this->assertFalse($numeric->validate());
    }

    public function testConfigOverridesOptions()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                  'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockEl->expects($this->once())->method('getRawValue')
               ->will($this->returnValue('foo'));

        HTML_QuickForm2_Factory::registerRule('regex-override', 'HTML_QuickForm2_Rule_Regex',
                                              null, '/^[a-zA-Z]+$/');
        $override = HTML_QuickForm2_Factory::createRule('regex-override', $mockEl,
                                                        'an error', '/^[0-9]+$/');
        $this->assertTrue($override->validate());
    }

    public function testBug10799()
    {
        $mockInvalid = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                      'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockInvalid->expects($this->once())->method('getRawValue')
                    ->will($this->returnValue("12345\n"));
        $ruleNumeric = new HTML_QuickForm2_Rule_Regex($mockInvalid, 'not valid',
                                                      '/(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/');
        $this->assertFalse($ruleNumeric->validate());
    }

    public function testCheckUploadFilename()
    {
        $mockValid = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
            ->setMethods(array('getValue'))
            ->getMock();
        $mockValid->expects($this->once())->method('getValue')
                  ->will($this->returnValue(array(
                    'name'     => 'pr0n.jpg',
                    'type'     => 'image/jpeg',
                    'tmp_name' => '/tmp/foobar',
                    'error'    => UPLOAD_ERR_OK,
                    'size'     => 123456
                  )));
        $rule = new HTML_QuickForm2_Rule_Regex($mockValid, 'an error', '/\\.(jpe?g|gif|png)$/i');
        $this->assertTrue($rule->validate());

        $mockInvalid = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
            ->setMethods(array('getValue'))
            ->getMock();
        $mockInvalid->expects($this->once())->method('getValue')
                    ->will($this->returnValue(array(
                      'name'     => 'trojan.exe',
                      'type'     => 'application/octet-stream',
                      'tmp_name' => '/tmp/quux',
                      'error'    => UPLOAD_ERR_OK,
                      'size'     => 98765
                    )));
        $rule = new HTML_QuickForm2_Rule_Regex($mockInvalid, 'an error', '/\\.(jpe?g|gif|png)$/i');
        $this->assertFalse($rule->validate());
    }

    public function testEmptyFieldsAreSkipped()
    {
        $mockEmpty = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                    'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockEmpty->expects($this->once())->method('getRawValue')
                  ->will($this->returnValue(''));
        $ruleSimple = new HTML_QuickForm2_Rule_Regex($mockEmpty, 'an error', '/^[a-zA-Z]+$/');
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
        $ruleFile = new HTML_QuickForm2_Rule_Regex($mockNoUpload, 'an error', '/\\.(jpe?g|gif|png)$/i');
        $this->assertTrue($ruleFile->validate());
    }

    public function testRequest12736()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                 'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockEl->expects($this->once())->method('getRawValue')
               ->will($this->returnValue('no Cyrillic letters here'));
        $ruleCyr = new HTML_QuickForm2_Rule_Regex($mockEl, 'an error', '/\x{0445}\x{0443}\x{0439}/ui');

        $this->assertFalse($ruleCyr->validate());
        $this->assertContains('/\\u0445\\u0443\\u0439/i.test(', $ruleCyr->getJavascript());
    }
}
?>
