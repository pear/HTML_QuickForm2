<?php
/**
 * Rule checking the value's length
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
 * @copyright 2006-2021 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

// pear-package-only /**
// pear-package-only  * Base class for HTML_QuickForm2 rules
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Rule.php';

/**
 * Rule checking the value's length
 *
 * The rule needs an "allowed length" parameter for its work, it can be either
 *  - a scalar: the value will be valid if it is exactly this long
 *  - an array: the value will be valid if its length is between the given values
 *    (inclusive). If one of these evaluates to 0, then length will be compared
 *    only with the remaining one.
 * See {@link mergeConfig()} for description of possible ways to pass
 * configuration parameters.
 *
 * The Rule considers empty fields as valid and doesn't try to compare their
 * lengths with provided limits.
 *
 * For convenience this Rule is also registered with the names 'minlength' and
 * 'maxlength' (having, respectively, 'max' and 'min' parameters set to 0):
 * <code>
 * $password->addRule('minlength', 'The password should be at least 6 characters long', 6);
 * $message->addRule('maxlength', 'Your message is too verbose', 1000);
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
class HTML_QuickForm2_Rule_Length extends HTML_QuickForm2_Rule
{
   /**
    * Validates the owner element
    *
    * @return   bool    whether length of the element's value is within allowed range
    */
    protected function validateOwner()
    {
        if (0 == ($valueLength = mb_strlen($this->owner->getValue(), 'UTF-8'))) {
            return true;
        }

        $allowedLength = $this->getConfig();
        if (is_scalar($allowedLength)) {
            return $valueLength == $allowedLength;
        } else {
            return (empty($allowedLength['min']) || $valueLength >= $allowedLength['min']) &&
                   (empty($allowedLength['max']) || $valueLength <= $allowedLength['max']);
        }
    }

    protected function getJavascriptCallback()
    {
        $allowedLength = $this->getConfig();
        if (is_scalar($allowedLength)) {
            $check = "length == {$allowedLength}";
        } else {
            $checks = [];
            if (!empty($allowedLength['min'])) {
                $checks[] = "length >= {$allowedLength['min']}";
            }
            if (!empty($allowedLength['max'])) {
                $checks[] = "length <= {$allowedLength['max']}";
            }
            $check = implode(' && ', $checks);
        }
        return "function() { var length = " . $this->owner->getJavascriptValue() .
               ".length; return qf.rules.empty(length) || ({$check}); }";
    }

   /**
    * Adds the 'min' and 'max' fields from one array to the other
    *
    * @param array $length Rule configuration, array with 'min' and 'max' keys
    * @param array $config Additional configuration, fields will be added to
    *                   $length if it doesn't contain such a key already
    *
    * @return   array
    */
    protected static function mergeMinMaxLength($length, $config)
    {
        if (array_key_exists('min', $config) || array_key_exists('max', $config)) {
            if (!array_key_exists('min', $length) && array_key_exists('min', $config)) {
                $length['min'] = $config['min'];
            }
            if (!array_key_exists('max', $length) && array_key_exists('max', $config)) {
                $length['max'] = $config['max'];
            }
        } else {
            if (!array_key_exists('min', $length)) {
                $length['min'] = reset($config);
            }
            if (!array_key_exists('max', $length)) {
                $length['max'] = end($config);
            }
        }
        return $length;
    }

   /**
    * Merges length limits given on rule creation with those given to registerRule()
    *
    * "Global" length limits may be passed to
    * {@link HTML_QuickForm2_Factory::registerRule()} in either of the
    * following formats
    *  - scalar (exact length)
    *  - array(minlength, maxlength)
    *  - array(['min' => minlength, ]['max' => maxlength])
    *
    * "Local" length limits may be passed to the constructor in either of
    * the following formats
    *  - scalar (if global config is unset then it is treated as an exact
    *    length, if 'min' or 'max' is in global config then it is treated
    *    as 'max' or 'min', respectively)
    *  - array(minlength, maxlength)
    *  - array(['min' => minlength, ]['max' => maxlength])
    *
    * As usual, global configuration overrides local one.
    *
    * @param int|array $localConfig  Local length limits
    * @param int|array $globalConfig Global length limits, usually provided to
    *                                {@link HTML_QuickForm2_Factory::registerRule()}
    *
    * @return   int|array   Merged length limits
    */
    public static function mergeConfig($localConfig, $globalConfig)
    {
        if (!isset($globalConfig)) {
            $length = $localConfig;

        } elseif (!is_array($globalConfig)) {
            $length = $globalConfig;

        } else {
            $length = self::mergeMinMaxLength([], $globalConfig);
            if (isset($localConfig)) {
                $length = self::mergeMinMaxLength(
                    $length, is_array($localConfig)? $localConfig: [$localConfig]
                );
            }
        }
        return $length;
    }

   /**
    * Sets the allowed length limits
    *
    * $config can be either of the following
    *  - integer (rule checks for exact length)
    *  - array(minlength, maxlength)
    *  - array(['min' => minlength, ]['max' => maxlength])
    *
    * @param int|array $config Length limits
    *
    * @return   HTML_QuickForm2_Rule
    * @throws   HTML_QuickForm2_InvalidArgumentException if bogus length limits
    *           were provided
    */
    public function setConfig($config)
    {
        if (is_array($config)) {
            $config = self::mergeMinMaxLength([], $config)
                      + ['min' => 0, 'max' => 0];
        }
        if (is_array($config) && ($config['min'] < 0 || $config['max'] < 0)
            || !is_array($config) && $config < 0
        ) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Length Rule requires limits to be nonnegative, ' .
                preg_replace('/\s+/', ' ', var_export($config, true)) . ' given'
            );

        } elseif (is_array($config) && $config['min'] == 0 && $config['max'] == 0
                  || !is_array($config) && 0 == $config
        ) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Length Rule requires at least one non-zero limit, ' .
                preg_replace('/\s+/', ' ', var_export($config, true)) . ' given'
            );
        }

        if (!empty($config['min']) && !empty($config['max'])) {
            if ($config['min'] > $config['max']) {
                list($config['min'], $config['max']) = [$config['max'], $config['min']];
            } elseif ($config['min'] == $config['max']) {
                $config = $config['min'];
            }
        }
        return parent::setConfig($config);
    }
}
?>
