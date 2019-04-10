<?php
/**
 * Validates all elements in a Container using a template Rule
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

// pear-package-only /**
// pear-package-only  * Base class for HTML_QuickForm2 rules
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Rule.php';

/**
 * Validates all elements in a Container using a template Rule
 *
 * This Rule needs one configuration parameter for its work: the template Rule
 * to use for actual validation. It can be passed either to
 * {@link HTML_QuickForm2_Rule::__construct() the Rule constructor} as local
 * configuration or to {@link HTML_QuickForm2_Factory::registerRule()} as
 * global one. As usual, global configuration overrides local.
 *
 * The container will be considered valid if all its elements are valid
 * according to a template Rule.
 *
 * <code>
 * $group->addRule('each', 'The fields should contain only letters',
 *                 $group->createRule('regex', '/^[a-z]+$/i'));
 * </code>
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Rule_Each extends HTML_QuickForm2_Rule
{
   /**
    * Validates the owner's children using the template Rule
    *
    * @return   bool    Whether all children are valid according to a template Rule
    */
    protected function validateOwner()
    {
        $rule = clone $this->getConfig();
        foreach ($this->owner->getRecursiveIterator(RecursiveIteratorIterator::LEAVES_ONLY) as $child) {
            try {
                $rule->setOwner($child);
                if (!$rule->validateOwner()) {
                    return false;
                }
            } catch (HTML_QuickForm2_InvalidArgumentException $e) {}
        }
        return true;
    }

   /**
    * Builds the callbacks for the owner's children using the template Rule
    *
    * @return   string    Javascript function calling all children's callbacks
    */
    protected function getJavascriptCallback()
    {
        $rule      = clone $this->getConfig();
        $callbacks = array();
        foreach ($this->owner->getRecursiveIterator(RecursiveIteratorIterator::LEAVES_ONLY) as $child) {
            try {
                $rule->setOwner($child);
                $callbacks[] = $rule->getJavascriptCallback();
            } catch (HTML_QuickForm2_InvalidArgumentException $e) {}
        }
        return "function () { return qf.rules.each([\n\t\t" . implode(",\n\t\t", $callbacks) . "\n\t]); }";
    }

   /**
    * Sets the template Rule to use for actual validation
    *
    * We do not allow using Required rules here, they are able to validate
    * containers themselves without the help of Each rule.
    *
    * @param HTML_QuickForm2_Rule $config Template Rule
    *
    * @return   HTML_QuickForm2_Rule
    * @throws   HTML_QuickForm2_InvalidArgumentException if $config is either not
    *               an instance of Rule or is an instance of Rule_Required
    */
    public function setConfig($config)
    {
        if (!$config instanceof HTML_QuickForm2_Rule) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Each Rule requires a template Rule to validate with, ' .
                preg_replace('/\s+/', ' ', var_export($config, true)) . ' given'
            );
        } elseif ($config instanceof HTML_QuickForm2_Rule_Required) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Cannot use "required" Rule as a template'
            );
        }
        return parent::setConfig($config);
    }

   /**
    * Sets the element that will be validated by this rule
    *
    * @param HTML_QuickForm2_Node $owner Container to validate
    *
    * @throws   HTML_QuickForm2_InvalidArgumentException    if trying to use
    *           this Rule on something that isn't a Container
    */
    public function setOwner(HTML_QuickForm2_Node $owner)
    {
        if (!$owner instanceof HTML_QuickForm2_Container) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Each Rule can only validate Containers, '.
                get_class($owner) . ' given'
            );
        }
        parent::setOwner($owner);
    }
}
?>