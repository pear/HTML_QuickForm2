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

if (!defined('PHPUnit_MAIN_METHOD')) {
    if (strpos($_SERVER['argv'][0], 'phpunit') === false) {
        define('PHPUnit_MAIN_METHOD', 'QuickForm2_AllTests::main');
    } else {
        define('PHPUnit_MAIN_METHOD', false);
    }
}

require_once dirname(__FILE__) . '/FactoryTest.php';
require_once dirname(__FILE__) . '/NodeTest.php';
require_once dirname(__FILE__) . '/ElementTest.php';
require_once dirname(__FILE__) . '/Element/AllTests.php';
require_once dirname(__FILE__) . '/ContainerTest.php';
require_once dirname(__FILE__) . '/ContainerOverloadTest.php';
require_once dirname(__FILE__) . '/Container/AllTests.php';
require_once dirname(__FILE__) . '/DataSource/AllTests.php';
require_once dirname(__FILE__) . '/RuleTest.php';
require_once dirname(__FILE__) . '/Rule/AllTests.php';
require_once dirname(__FILE__) . '/FilterTest.php';
require_once dirname(__FILE__) . '/RendererTest.php';
require_once dirname(__FILE__) . '/Renderer/AllTests.php';
require_once dirname(__FILE__) . '/ControllerTest.php';
require_once dirname(__FILE__) . '/Controller/AllTests.php';
require_once dirname(__FILE__) . '/JavascriptBuilderTest.php';

class QuickForm2_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('HTML_QuickForm2 package - QuickForm2');

        $suite->addTestSuite('HTML_QuickForm2_FactoryTest');
        $suite->addTestSuite('HTML_QuickForm2_NodeTest');
        $suite->addTestSuite('HTML_QuickForm2_ElementTest');
        $suite->addTestSuite('HTML_QuickForm2_ContainerTest');
        $suite->addTestSuite('HTML_QuickForm2_ContainerOverloadTest');
        $suite->addTestSuite('HTML_QuickForm2_RuleTest');
        $suite->addTestSuite('HTML_QuickForm2_FilterTest');
        $suite->addTestSuite('HTML_QuickForm2_RendererTest');
        $suite->addTestSuite('HTML_QuickForm2_ControllerTest');
        $suite->addTestSuite('HTML_QuickForm2_JavascriptBuilderTest');
        $suite->addTest(QuickForm2_Element_AllTests::suite());
        $suite->addTest(QuickForm2_Container_AllTests::suite());
        $suite->addTest(QuickForm2_DataSource_AllTests::suite());
        $suite->addTest(QuickForm2_Rule_AllTests::suite());
        $suite->addTest(QuickForm2_Renderer_AllTests::suite());
        $suite->addTest(QuickForm2_Controller_AllTests::suite());

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'QuickForm2_AllTests::main') {
    QuickForm2_AllTests::main();
}
?>