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
require_once dirname(__FILE__) . '/TestHelper.php';

class FormRule extends HTML_QuickForm2_Rule
{
    protected function validateOwner()
    {
        return false;
    }

    protected function setOwnerError()
    {
        $this->owner->getElementById('foo')->setError('an error message');
    }
}


/**
 * Unit test for HTML_QuickForm2 class
 */
class HTML_QuickForm2Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_REQUEST = array(
            '_qf__track' => ''
        );
        $_GET = array(
            'key' => 'value'
        );
        $_POST = array();
    }

    public function testTrackSubmit()
    {
        $form1 = new HTML_QuickForm2('track', 'post');
        $this->assertEquals(1, count($form1->getDataSources()));

        $form2 = new HTML_QuickForm2('track', 'post', null, false);
        $this->assertEquals(0, count($form2->getDataSources()));

        $form3 = new HTML_QuickForm2('track', 'get');
        $this->assertEquals(1, count($form3->getDataSources()));

        $form4 = new HTML_QuickForm2('notrack', 'get');
        $this->assertEquals(0, count($form4->getDataSources()));

        $form2 = new HTML_QuickForm2('notrack', 'get', null, false);
        $this->assertEquals(1, count($form2->getDataSources()));
    }

    public function testConstructorSetsIdAndMethod()
    {
        $form1 = new HTML_QuickForm2(null);
        $this->assertEquals('post', $form1->getAttribute('method'));
        $this->assertNotEquals(0, strlen($form1->getAttribute('id')));

        $form2 = new HTML_QuickForm2('foo', 'get');
        $this->assertEquals('get', $form2->getAttribute('method'));
        $this->assertEquals('foo', $form2->getAttribute('id'));

        $form3 = new HTML_QuickForm2('bar', 'post', array('method' => 'get', 'id' => 'whatever'));
        $this->assertEquals('post', $form3->getAttribute('method'));
        $this->assertEquals('bar', $form3->getAttribute('id'));
    }

    public function testConstructorSetsDefaultAction()
    {
        $form1 = new HTML_QuickForm2('test');
        $this->assertEquals($_SERVER['PHP_SELF'], $form1->getAttribute('action'));

        $form2 = new HTML_QuickForm2('test2', 'post', array('action' => '/foobar.php'));
        $this->assertEquals('/foobar.php', $form2->getAttribute('action'));
    }

    public function testIdAndMethodAreReadonly()
    {
        $form = new HTML_QuickForm2('foo', 'get');

        try {
            $form->removeAttribute('id');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            try {
                $form->setAttribute('method', 'post');
            } catch (HTML_QuickForm2_InvalidArgumentException $e) {
                try {
                    $form->setId('newId');
                } catch (HTML_QuickForm2_InvalidArgumentException $e) {
                    return;
                }
            }
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }

    public function testCannotAddToContainer()
    {
        $form1 = new HTML_QuickForm2('form1');
        $form2 = new HTML_QuickForm2('form2');

        try {
            $form1->appendChild($form2);
        } catch (HTML_QuickForm2_Exception $e) {
            return;
        }
        $this->fail('Expected HTML_QuickForm2_Exception was not thrown');
    }

    public function testSetDataSources()
    {
        $ds1 = new HTML_QuickForm2_DataSource_Array(array('key' => 'value'));
        $ds2 = new HTML_QuickForm2_DataSource_Array(array('another key' => 'foo'));

        $form = new HTML_QuickForm2('dstest');
        $this->assertEquals(0, count($form->getDataSources()));
        $form->addDataSource($ds2);
        $this->assertEquals(1, count($form->getDataSources()));

        $form->setDataSources(array($ds1, $ds2));
        $this->assertEquals(2, count($form->getDataSources()));

        try {
            $form->setDataSources(array($ds1, 'bogus', $ds2));
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            return;
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }

    public function testValidateChecksWhetherFormIsSubmitted()
    {
        $form1 = new HTML_QuickForm2('notrack', 'post');
        $this->assertFalse($form1->validate());

        $form2 = new HTML_QuickForm2('track', 'post');
        $this->assertTrue($form2->validate());
    }

    public function testFormRule()
    {
        $form = new HTML_QuickForm2('track', 'post');
        $foo = $form->addElement('text', 'foo', array('id' => 'foo'));
        $form->addRule(new FormRule($form));

        $this->assertFalse($form->validate());
        $this->assertEquals('an error message', $foo->getError());
    }

    /**
     * Do not return values for automatically added elements from getValue()
     * @link http://pear.php.net/bugs/bug.php?id=19403
     */
    public function testRequest19403()
    {
        $_POST = array('_qf__track' => '');
        $form  = new HTML_QuickForm2('track');

        $this->assertArrayHasKey('_qf__track', $form->getRawValue());
        $this->assertArrayNotHasKey('_qf__track', $form->getValue());
    }
}
?>
