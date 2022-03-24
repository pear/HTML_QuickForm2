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
 * @copyright 2006-2022 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

/**
 * An "implementation" of renderer, to be able to create an instance
 */
class FakeRenderer extends HTML_QuickForm2_Renderer
{
    public $name = 'fake';

    public function renderElement(HTML_QuickForm2_Node $element) {}
    public function renderHidden(HTML_QuickForm2_Node $element) {}
    public function startForm(HTML_QuickForm2_Node $form) {}
    public function finishForm(HTML_QuickForm2_Node $form) {}
    public function startContainer(HTML_QuickForm2_Node $container) {}
    public function finishContainer(HTML_QuickForm2_Node $container) {}
    public function startGroup(HTML_QuickForm2_Node $group) {}
    public function finishGroup(HTML_QuickForm2_Node $group) {}
    public function reset() {}
}

?>
