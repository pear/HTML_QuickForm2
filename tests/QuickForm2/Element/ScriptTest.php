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
 * Unit test for HTML_QuickForm2_Element_Select class
 */
class HTML_QuickForm2_Element_ScriptTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        HTML_Common2::setOption('nonce', null);
    }

    public function testInlineScriptNonce()
    {
        $element = new HTML_QuickForm2_Element_Script();
        $element->setContent('Some javascript');

        $script = $element->__toString();
        $this->assertNotRegExp('/<script[^>]*nonce/', $script);

        HTML_Common2::setOption(
            'nonce',
            $nonce = base64_encode('HTML_QuickForm2_nonce' . microtime())
        );
        $script = $element->__toString();
        $this->assertRegExp('/<script[^>]*nonce="' . $nonce . '"/', $script);
    }
}