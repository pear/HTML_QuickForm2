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
 * A non-abstract subclass of Node
 *
 * We can't instantiate the class directly and thus need to "implement" its
 * abstract methods. And also make validate() public to be able to test.
 */
class NodeImpl extends HTML_QuickForm2_Node
{
    public function getType() { return 'concrete'; }
    public function getRawValue() { return ''; }
    public function setValue($value) { return ''; }
    public function __toString() { return ''; }

    public function getName() { return ''; }
    public function setName($name) { }

    protected function updateValue() { }

    public function validate() { return parent::validate(); }

    public function getJavascriptValue($inContainer = false) { return ''; }
    public function getJavascriptTriggers() { return []; }

    public function render(HTML_QuickForm2_Renderer $renderer) { }
}
