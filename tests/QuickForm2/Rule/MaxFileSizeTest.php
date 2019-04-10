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
 * Unit test for HTML_QuickForm2_Rule_MaxFileSize class
 */
class HTML_QuickForm2_Rule_MaxFileSizeTest extends PHPUnit_Framework_TestCase
{
    public function testPositiveSizeLimitIsRequired()
    {
        $file    = new HTML_QuickForm2_Element_InputFile('foo');
        try {
            $maxSize = new HTML_QuickForm2_Rule_MaxFileSize($file, 'an error');
            $this->fail('The expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/MaxFileSize Rule requires a positive size limit/', $e->getMessage());
        }
        try {
            $maxSizeNegative = new HTML_QuickForm2_Rule_MaxFileSize($file, 'an error', -10);
            $this->fail('The expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/MaxFileSize Rule requires a positive size limit/', $e->getMessage());
        }
    }

    public function testCanOnlyValidateFileUploads()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                  'getRawValue', 'setValue', '__toString'))
            ->getMock();
        try {
            $maxSize = new HTML_QuickForm2_Rule_MaxFileSize($mockEl, 'an error', 1024);
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/MaxFileSize Rule can only validate file upload fields/', $e->getMessage());
            return;
        }
        $this->fail('The expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }

    public function testMissingUploadsAreSkipped()
    {
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
        $maxSize = new HTML_QuickForm2_Rule_MaxFileSize($mockNoUpload, 'an error', 1024);
        $this->assertTrue($maxSize->validate());
    }

    public function testOptionsHandling()
    {
        $mockFile = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
            ->setMethods(array('getValue'))
            ->getMock();
        $mockFile->expects($this->exactly(2))->method('getValue')
                 ->will($this->returnValue(array(
                    'name'     => 'pr0n.jpg',
                    'type'     => 'image/jpeg',
                    'tmp_name' => dirname(dirname(__FILE__)) . '/_files/1024-bytes.upload',
                    'error'    => UPLOAD_ERR_OK,
                    'size'     => 1024
                 )));
        $size512 = new HTML_QuickForm2_Rule_MaxFileSize($mockFile, 'too big', 512);
        $this->assertFalse($size512->validate());

        $size2048 = new HTML_QuickForm2_Rule_MaxFileSize($mockFile, 'too big', 2048);
        $this->assertTrue($size2048->validate());
    }

    public function testConfigHandling()
    {
        $mockFile = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
            ->setMethods(array('getValue'))
            ->getMock();
        $mockFile->expects($this->exactly(2))->method('getValue')
                 ->will($this->returnValue(array(
                    'name'     => 'pr0n.jpg',
                    'type'     => 'image/jpeg',
                    'tmp_name' => dirname(dirname(__FILE__)) . '/_files/1024-bytes.upload',
                    'error'    => UPLOAD_ERR_OK,
                    'size'     => 1024
                 )));

        HTML_QuickForm2_Factory::registerRule('filesize-512', 'HTML_QuickForm2_Rule_MaxFileSize',
                                              null, 512);
        $size512  = $mockFile->addRule('filesize-512', 'too big');
        $this->assertFalse($size512->validate());

        HTML_QuickForm2_Factory::registerRule('filesize-2048', 'HTML_QuickForm2_Rule_MaxFileSize',
                                              null, 2048);
        $size2048 = $mockFile->addRule('filesize-2048', 'too big');
        $this->assertTrue($size2048->validate());
    }

    public function testConfigOverridesOptions()
    {
        $mockFile = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
            ->setMethods(array('getValue'))
            ->getMock();
        $mockFile->expects($this->once())->method('getValue')
                 ->will($this->returnValue(array(
                    'name'     => 'pr0n.jpg',
                    'type'     => 'image/jpeg',
                    'tmp_name' => dirname(dirname(__FILE__)) . '/_files/1024-bytes.upload',
                    'error'    => UPLOAD_ERR_OK,
                    'size'     => 1024
                 )));
        HTML_QuickForm2_Factory::registerRule('filesize-override-512', 'HTML_QuickForm2_Rule_MaxFileSize',
                                              null, 512);
        $maxSize = $mockFile->addRule('filesize-override-512', 'too big', 10240);
        $this->assertFalse($maxSize->validate());
    }
}
?>
