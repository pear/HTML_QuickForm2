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
        define('PHPUnit_MAIN_METHOD', 'QuickForm2_Element_AllTests::main');
    } else {
        define('PHPUnit_MAIN_METHOD', false);
    }
}

require_once dirname(__FILE__) . '/InputTest.php';
require_once dirname(__FILE__) . '/SelectTest.php';
require_once dirname(__FILE__) . '/TextareaTest.php';
require_once dirname(__FILE__) . '/InputCheckableTest.php';
require_once dirname(__FILE__) . '/InputCheckboxTest.php';
require_once dirname(__FILE__) . '/InputPasswordTest.php';
require_once dirname(__FILE__) . '/InputImageTest.php';
require_once dirname(__FILE__) . '/InputHiddenTest.php';
require_once dirname(__FILE__) . '/InputSubmitTest.php';
require_once dirname(__FILE__) . '/InputButtonTest.php';
require_once dirname(__FILE__) . '/InputResetTest.php';
require_once dirname(__FILE__) . '/ButtonTest.php';
require_once dirname(__FILE__) . '/InputFileTest.php';
require_once dirname(__FILE__) . '/StaticTest.php';
require_once dirname(__FILE__) . '/DateTest.php';
require_once dirname(__FILE__) . '/HierselectTest.php';
require_once dirname(__FILE__) . '/ScriptTest.php';

class QuickForm2_Element_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('HTML_QuickForm2 package - QuickForm2 - Element');

        $suite->addTestSuite('HTML_QuickForm2_Element_InputTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_SelectTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_TextareaTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_InputCheckableTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_InputCheckboxTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_InputPasswordTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_InputImageTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_InputHiddenTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_InputSubmitTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_InputButtonTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_InputResetTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_ButtonTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_InputFileTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_StaticTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_DateTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_HierselectTest');
        $suite->addTestSuite('HTML_QuickForm2_Element_ScriptTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'QuickForm2_Element_AllTests::main') {
    QuickForm2_Element_AllTests::main();
}

?>