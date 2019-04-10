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
 * Unit test for HTML_QuickForm2_JavascriptBuilder class
 */
class HTML_QuickForm2_JavascriptBuilderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        HTML_Common2::setOption('nonce', null);
    }

    public function testEncode()
    {
        $this->assertEquals('null', HTML_QuickForm2_JavascriptBuilder::encode(null));
        $this->assertEquals('false', HTML_QuickForm2_JavascriptBuilder::encode(false));
        $this->assertEquals('"foo"', HTML_QuickForm2_JavascriptBuilder::encode('foo'));
        $this->assertEquals('"\r\n\t\\\'\"bar\\\\"', HTML_QuickForm2_JavascriptBuilder::encode("\r\n\t'\"bar\\"));
        $this->assertEquals(1, HTML_QuickForm2_JavascriptBuilder::encode(1));

        $this->assertEquals('[]', HTML_QuickForm2_JavascriptBuilder::encode(array()));
        $this->assertEquals('{}', HTML_QuickForm2_JavascriptBuilder::encode(new stdClass()));

        $this->assertEquals('["a","b"]', HTML_QuickForm2_JavascriptBuilder::encode(array('a', 'b')));
        $this->assertEquals('{"0":"a","b":"c"}', HTML_QuickForm2_JavascriptBuilder::encode(array('a', 'b' => 'c')));

        $obj = new stdClass();
        $obj->a = 'b';
        $obj->c = 'd';
        $obj->e = array('f', 'g');
        $this->assertEquals('{"a":"b","c":"d","e":["f","g"]}', HTML_QuickForm2_JavascriptBuilder::encode($obj));

        try {
            $fp = fopen(__FILE__, 'rb');
            HTML_QuickForm2_JavascriptBuilder::encode($fp);
            $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {}
        fclose($fp);
    }

    public function testBaseLibrary()
    {
        $builder = new HTML_QuickForm2_JavascriptBuilder();

        $libraries = $builder->getLibraries(false, false);
        $this->assertArrayHasKey('base', $libraries);
        $this->assertNotContains('<script', $libraries['base']);

        $libraries = $builder->getLibraries(false, true);
        $this->assertContains('<script', $libraries['base']);

        $libraries = $builder->getLibraries(true, false);
        $this->assertContains('qf.Validator', $libraries);
        $this->assertNotContains('<script', $libraries);

        $libraries = $builder->getLibraries(true, true);
        $this->assertContains('qf.Validator', $libraries);
        $this->assertContains('<script', $libraries);
    }

    public function testInlineLibraryNonce()
    {
        $builder = new HTML_QuickForm2_JavascriptBuilder();

        $libraries = $builder->getLibraries(true, true);
        $this->assertNotRegExp('/<script[^>]*nonce/', $libraries);

        HTML_Common2::setOption(
            'nonce',
            $nonce = base64_encode('HTML_QuickForm2_nonce' . microtime())
        );
        $libraries = $builder->getLibraries(true, true);
        $this->assertRegExp('/<script[^>]*nonce="' . $nonce . '"/', $libraries);
    }

    public function testInlineMissingLibrary()
    {
        $builder = new HTML_QuickForm2_JavascriptBuilder();
        $builder->addLibrary('missing', 'missing.js');

        try {
            $libraries = $builder->getLibraries(true);
            $this->fail('Expected HTML_QuickForm2_NotFoundException was not thrown');
        } catch (HTML_QuickForm2_NotFoundException $e) { }
    }

    public function testFormJavascript()
    {
        $builder = new HTML_QuickForm2_JavascriptBuilder();
        $element = new HTML_QuickForm2_Element_InputText();

        $mockRuleOne = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(array('validateOwner', 'getJavascriptCallback'))
            ->setConstructorArgs(array($element))
            ->getMock();
        $mockRuleOne->expects($this->once())->method('getJavascriptCallback')
            ->will($this->returnValue('jsRuleOne'));

        $mockRuleTwo = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(array('validateOwner', 'getJavascriptCallback'))
            ->setConstructorArgs(array($element))
            ->getMock();
        $mockRuleTwo->expects($this->once())->method('getJavascriptCallback')
            ->will($this->returnValue('jsRuleTwo'));

        $this->assertEquals('', $builder->getFormJavascript());

        $builder->setFormId('formOne');
        $builder->addRule($mockRuleOne);
        $builder->addElementJavascript('setupCodeOne');

        $builder->setFormId('formTwo');
        $builder->addRule($mockRuleTwo);
        $builder->addElementJavascript('setupCodeTwo');

        $scriptOne = $builder->getFormJavascript('formOne', false);
        $this->assertContains('jsRuleOne', $scriptOne);
        $this->assertContains('setupCodeOne', $scriptOne);
        $this->assertNotContains('jsRuleTwo', $scriptOne);
        $this->assertNotContains('setupCodeTwo', $scriptOne);
        $this->assertNotContains('<script', $scriptOne);

        $scriptTwo = $builder->getFormJavascript('formTwo', true);
        $this->assertNotContains('jsRuleOne', $scriptTwo);
        $this->assertNotContains('setupCodeOne', $scriptTwo);
        $this->assertContains('jsRuleTwo', $scriptTwo);
        $this->assertContains('setupCodeTwo', $scriptTwo);
        $this->assertContains('<script', $scriptTwo);

        $scriptBoth = $builder->getFormJavascript();
        $this->assertContains('jsRuleOne', $scriptBoth);
        $this->assertContains('setupCodeTwo', $scriptBoth);
    }

    public function testFormJavascriptNonce()
    {
        $builder = new HTML_QuickForm2_JavascriptBuilder();
        $builder->addElementJavascript('Some setup code');

        $script = $builder->getFormJavascript();
        $this->assertContains('Some setup code', $script);
        $this->assertNotRegExp('/<script[^>]*nonce/', $script);

        HTML_Common2::setOption(
            'nonce',
            $nonce = base64_encode('HTML_QuickForm2_nonce' . microtime())
        );
        $script = $builder->getFormJavascript();
        $this->assertRegExp('/<script[^>]*nonce="' . $nonce . '"/', $script);
    }
}
?>