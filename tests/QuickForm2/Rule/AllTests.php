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
        define('PHPUnit_MAIN_METHOD', 'QuickForm2_Rule_AllTests::main');
    } else {
        define('PHPUnit_MAIN_METHOD', false);
    }
}

require_once dirname(__FILE__) . '/NonemptyTest.php';
require_once dirname(__FILE__) . '/RequiredTest.php';
require_once dirname(__FILE__) . '/CompareTest.php';
require_once dirname(__FILE__) . '/EmptyTest.php';
require_once dirname(__FILE__) . '/RegexTest.php';
require_once dirname(__FILE__) . '/CallbackTest.php';
require_once dirname(__FILE__) . '/LengthTest.php';
require_once dirname(__FILE__) . '/MaxFileSizeTest.php';
require_once dirname(__FILE__) . '/MimeTypeTest.php';
require_once dirname(__FILE__) . '/EachTest.php';
require_once dirname(__FILE__) . '/NotCallbackTest.php';
require_once dirname(__FILE__) . '/NotRegexTest.php';
require_once dirname(__FILE__) . '/EmailTest.php';

class QuickForm2_Rule_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('HTML_QuickForm2 package - QuickForm2 - Rule');

        $suite->addTestSuite('HTML_QuickForm2_Rule_NonemptyTest');
        $suite->addTestSuite('HTML_QuickForm2_Rule_RequiredTest');
        $suite->addTestSuite('HTML_QuickForm2_Rule_CompareTest');
        $suite->addTestSuite('HTML_QuickForm2_Rule_EmptyTest');
        $suite->addTestSuite('HTML_QuickForm2_Rule_RegexTest');
        $suite->addTestSuite('HTML_QuickForm2_Rule_CallbackTest');
        $suite->addTestSuite('HTML_QuickForm2_Rule_LengthTest');
        $suite->addTestSuite('HTML_QuickForm2_Rule_MaxFileSizeTest');
        $suite->addTestSuite('HTML_QuickForm2_Rule_MimeTypeTest');
        $suite->addTestSuite('HTML_QuickForm2_Rule_EachTest');
        $suite->addTestSuite('HTML_QuickForm2_Rule_NotCallbackTest');
        $suite->addTestSuite('HTML_QuickForm2_Rule_NotRegexTest');
        $suite->addTestSuite('HTML_QuickForm2_Rule_EmailTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'QuickForm2_Rule_AllTests::main') {
    QuickForm2_Rule_AllTests::main();
}
?>