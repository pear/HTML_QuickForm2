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
        define('PHPUnit_MAIN_METHOD', 'QuickForm2_Controller_AllTests::main');
    } else {
        define('PHPUnit_MAIN_METHOD', false);
    }
}

require_once dirname(__FILE__) . '/PageTest.php';
require_once dirname(__FILE__) . '/Action/AllTests.php';

class QuickForm2_Controller_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('HTML_QuickForm2 package - QuickForm2 - Controller');

        $suite->addTestSuite('HTML_QuickForm2_Controller_PageTest');
        $suite->addTest(QuickForm2_Controller_Action_AllTests::suite());

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'QuickForm2_Controller_AllTests::main') {
    QuickForm2_Controller_AllTests::main();
}
?>