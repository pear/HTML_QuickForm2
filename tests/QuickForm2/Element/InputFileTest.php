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
 * Unit test for HTML_QuickForm2_Element_InputFile class
 */
class HTML_QuickForm2_Element_InputFileTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_FILES = array(
            'foo' => array(
                'name'      => 'file.doc',
                'tmp_name'  => '/tmp/nothing',
                'type'      => 'text/plain',
                'size'      => 1234,
                'error'     => UPLOAD_ERR_OK
            ),
            'toobig' => array(
                'name'      => 'ahugefile.zip',
                'tmp_name'  => '',
                'type'      => '',
                'size'      => 0,
                'error'     => UPLOAD_ERR_FORM_SIZE
            ),
            'local' => array(
                'name'      => 'nasty-trojan.exe',
                'tmp_name'  => '',
                'type'      => '',
                'size'      => 0,
                'error'     => UPLOAD_ERR_CANT_WRITE
            )
        );
        $_POST = array(
            'MAX_FILE_SIZE' => '987654'
        );
    }

    public function testCannotBeFrozen()
    {
        $upload = new HTML_QuickForm2_Element_InputFile('foo');
        $this->assertFalse($upload->toggleFrozen(true));
        $this->assertFalse($upload->toggleFrozen());
    }

    public function testSetValueFromSubmitDataSource()
    {
        $form = new HTML_QuickForm2('upload', 'post', null, false);
        $foo = $form->appendChild(new HTML_QuickForm2_Element_InputFile('foo'));
        $bar = $form->appendChild(new HTML_QuickForm2_Element_InputFile('bar'));

        $this->assertNull($bar->getValue());
        $this->assertEquals(array(
            'name'      => 'file.doc',
            'tmp_name'  => '/tmp/nothing',
            'type'      => 'text/plain',
            'size'      => 1234,
            'error'     => UPLOAD_ERR_OK
        ), $foo->getValue());
    }

    public function testBuiltinValidation()
    {
        $form = new HTML_QuickForm2('upload', 'post', null, false);
        $foo  = $form->appendChild(new HTML_QuickForm2_Element_InputFile('foo'));
        $this->assertTrue($form->validate());

        $toobig = $form->appendChild(new HTML_QuickForm2_Element_InputFile('toobig'));
        $this->assertFalse($form->validate());
        $this->assertContains('987654', $toobig->getError());
    }

   /**
    * @expectedException HTML_QuickForm2_InvalidArgumentException
    */
    public function testInvalidMessageProvider()
    {
        $invalid = new HTML_QuickForm2_Element_InputFile('invalid', null, array('messageProvider' => array()));
    }

    public static function callbackMessageProvider($messageId, $langId)
    {
        return "A nasty error happened!";
    }

    public function testCallbackMessageProvider()
    {
        $form   = new HTML_QuickForm2('upload', 'post', null, false);
        $upload = $form->addFile('local', array(), array(
            'messageProvider' => array(__CLASS__, 'callbackMessageProvider')
        ));
        $this->assertFalse($form->validate());
        $this->assertEquals('A nasty error happened!', $upload->getError());
    }

    public function testObjectMessageProvider()
    {
        $mockProvider = $this->getMockBuilder('HTML_QuickForm2_MessageProvider')
            ->setMethods(array('get'))
            ->getMock();
        $mockProvider->expects($this->once())->method('get')
                     ->will($this->returnValue('A nasty error happened!'));

        $form   = new HTML_QuickForm2('upload', 'post', null, false);
        $upload = $form->addFile('local', array(), array(
            'messageProvider' => $mockProvider
        ));
        $this->assertFalse($form->validate());
        $this->assertEquals('A nasty error happened!', $upload->getError());
    }

   /**
    * File should check that the form has POST method, set enctype to multipart/form-data
    * @see http://pear.php.net/bugs/bug.php?id=16807
    */
    public function testRequest16807()
    {
        $form = new HTML_QuickForm2('broken', 'get');

        try {
            $form->addFile('upload', array('id' => 'upload'));
            $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {}

        $group = HTML_QuickForm2_Factory::createElement('group', 'fileGroup');
        $group->addFile('upload', array('id' => 'upload'));
        try {
            $form->appendChild($group);
            $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {}

        $post = new HTML_QuickForm2('okform', 'post');
        $this->assertNull($post->getAttribute('enctype'));
        $post->addFile('upload');
        $this->assertEquals('multipart/form-data', $post->getAttribute('enctype'));
    }
}
?>
