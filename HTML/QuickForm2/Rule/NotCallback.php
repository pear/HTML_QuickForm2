<?php
/**
 * Rule checking the value via a callback function (method) with logical negation
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

// pear-package-only /**
// pear-package-only  * Rule checking the value via a callback function (method)
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Rule/Callback.php';

/**
 * Rule checking the value via a callback function (method) with logical negation
 *
 * The Rule accepts the same configuration parameters as the Callback Rule
 * does, but the callback is expected to return false if the element is valid
 * and true if it is invalid.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Rule_NotCallback extends HTML_QuickForm2_Rule_Callback
{
   /**
    * Validates the owner element
    *
    * @return   bool    negated result of a callback function
    */
    protected function validateOwner()
    {
        $value  = $this->owner->getValue();
        $config = $this->getConfig();
        return !call_user_func_array(
            $config['callback'], array_merge([$value], $config['arguments'])
        );
    }

    protected function getJavascriptCallback()
    {
        // pear-package-only HTML_QuickForm2_Loader::loadClass('HTML_QuickForm2_JavascriptBuilder');

        $config    = $this->getConfig();
        $arguments = [$this->owner->getJavascriptValue()];
        foreach ($config['arguments'] as $arg) {
            $arguments[] = HTML_QuickForm2_JavascriptBuilder::encode($arg);
        }
        return "function() { return !" . $this->findJavascriptName() .
               "(" . implode(', ', $arguments) . "); }";
    }
}
?>