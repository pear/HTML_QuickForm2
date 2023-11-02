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
 * @copyright 2006-2023 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

/** Sets up includes */
require_once dirname(dirname(__DIR__)) . '/TestHelper.php';

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Unit test for HTML_QuickForm2_Element_InputHidden class
 */
class HTML_QuickForm2_Element_InputHiddenTest extends TestCase
{
    public function testCannotBeFrozen()
    {
        $hidden = new HTML_QuickForm2_Element_InputHidden('foo');
        $this->assertFalse($hidden->toggleFrozen(true));
        $this->assertFalse($hidden->toggleFrozen());
    }

    public function testCannotSetError()
    {
        $this::expectException(\HTML_QuickForm2_InvalidArgumentException::class);
        $hidden = new HTML_QuickForm2_Element_InputHidden('noError');
        $hidden->setError('a message');
    }
}
?>
