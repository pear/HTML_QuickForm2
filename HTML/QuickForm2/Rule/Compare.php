<?php
/**
 * Rule comparing the value of the field with some other value
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
// pear-package-only  * Base class for HTML_QuickForm2 rules
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Rule.php';

/**
 * Rule comparing the value of the field with some other value
 *
 * The Rule needs two configuration parameters for its work
 *  - comparison operator (defaults to equality)
 *  - operand to compare with; this can be either a constant or another form
 *    element (its value will be used)
 * See {@link mergeConfig()} for description of possible ways to pass
 * configuration parameters.
 *
 * Note that 'less than [or equal]' and 'greater than [or equal]' operators
 * compare the operands numerically, since this is considered as more useful
 * approach by the authors.
 *
 * For convenience, this Rule is already registered in the Factory with the
 * names 'eq', 'neq', 'lt', 'gt', 'lte', 'gte' corresponding to the relevant
 * operators:
 * <code>
 * $password->addRule('eq', 'Passwords do not match', $passwordRepeat);
 * $orderQty->addRule('lte', 'Should not order more than 10 of these', 10);
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
class HTML_QuickForm2_Rule_Compare extends HTML_QuickForm2_Rule
{
   /**
    * Possible comparison operators
    * @var array
    */
    protected $operators = ['==', '!=', '===', '!==', '<', '<=', '>', '>='];


   /**
    * Validates the owner element
    *
    * @return   bool    whether (element_value operator operand) expression is true
    */
    protected function validateOwner()
    {
        $value   = $this->owner->getValue();
        $config  = $this->getConfig();
        $operand = $config['operand'] instanceof HTML_QuickForm2_Node
                   ? $config['operand']->getValue(): $config['operand'];

        switch ($config['operator']) {
        case '===': return strval($value) === strval($operand);
        case '!==': return strval($value) !== strval($operand);
        case '>':   return floatval($value) > floatval($operand);
        case '>=':  return floatval($value) >= floatval($operand);
        case '<':   return floatval($value) < floatval($operand);
        case '<=':  return floatval($value) <= floatval($operand);
        default:    return false;
        }
    }

    protected function getJavascriptCallback()
    {
        // pear-package-only HTML_QuickForm2_Loader::loadClass('HTML_QuickForm2_JavascriptBuilder');

        $config   = $this->getConfig();
        $operand1 = $this->owner->getJavascriptValue();
        $operand2 = $config['operand'] instanceof HTML_QuickForm2_Node
                    ? $config['operand']->getJavascriptValue()
                    : HTML_QuickForm2_JavascriptBuilder::encode($config['operand']);

        if (!in_array($config['operator'], ['===', '!=='])) {
            $check = "Number({$operand1}) {$config['operator']} Number({$operand2})";
        } else {
            $check = "String({$operand1}) {$config['operator']} String({$operand2})";
        }

        return "function () { return {$check}; }";
    }

    protected function getOwnJavascriptTriggers()
    {
        $triggers = $this->owner->getJavascriptTriggers();
        $config   = $this->getConfig();
        if ($config['operand'] instanceof HTML_QuickForm2_Node) {
            $triggers = array_merge($triggers, $config['operand']->getJavascriptTriggers());
        }
        return $triggers;
    }

   /**
    * Merges local configuration with that provided for registerRule()
    *
    * "Global" configuration may be passed to
    * {@link HTML_QuickForm2_Factory::registerRule()} in
    * either of the following formats
    *  - operator
    *  - array(operator[, operand])
    *  - array(['operator' => operator, ]['operand' => operand])

    * "Local" configuration may be passed to the constructor in either of
    * the following formats
    *  - operand
    *  - array([operator, ]operand)
    *  - array(['operator' => operator, ]['operand' => operand])
    *
    * As usual, global configuration overrides local one.
    *
    * @param mixed $localConfig  Local configuration
    * @param mixed $globalConfig Global configuration
    *
    * @return   mixed   Merged configuration
    */
    public static function mergeConfig($localConfig, $globalConfig)
    {
        $config = null;
        if (null !== $globalConfig && [] !== $globalConfig) {
            $config = self::toCanonicalForm($globalConfig, 'operator');
        }
        if (null !== $localConfig && [] !== $localConfig) {
            $config = (isset($config)? $config: [])
                      + self::toCanonicalForm($localConfig);
        }
        return $config;
    }

   /**
    * Converts configuration data to a canonical associative array form
    *
    * @param mixed  $config Configuration data
    * @param string $key    Array key to assign $config to if it is scalar
    *
    * @return   array   Associative array that may contain 'operand' and 'operator' keys
    */
    protected static function toCanonicalForm($config, $key = 'operand')
    {
        if (!is_array($config)) {
            return [$key => $config];

        } elseif (array_key_exists('operator', $config)
                  || array_key_exists('operand', $config)
        ) {
            return $config;

        } elseif (1 == count($config)) {
            return [$key => end($config)];

        } else {
            return ['operator' => reset($config), 'operand' => end($config)];
        }
    }

   /**
    * Sets the comparison operator and operand to compare to
    *
    * $config can be either of the following
    *  - operand
    *  - array([operator, ]operand)
    *  - array(['operator' => operator, ]['operand' => operand])
    * If operator is missing it will default to '==='
    *
    * @param mixed $config Configuration data
    *
    * @return   $this
    * @throws   HTML_QuickForm2_InvalidArgumentException if a bogus comparison
    *           operator is used for configuration, if an operand is missing
    */
    public function setConfig($config)
    {
        if (null === $config || [] === $config) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Compare Rule requires an argument to compare with'
            );
        }
        $config = self::toCanonicalForm($config);

        $config += ['operator' => '==='];
        if (!in_array($config['operator'], $this->operators)) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Compare Rule requires a valid comparison operator, ' .
                preg_replace('/\s+/', ' ', var_export($config['operator'], true)) . ' given'
            );
        }
        if (in_array($config['operator'], ['==', '!='])) {
            $config['operator'] .= '=';
        }

        return parent::setConfig($config);
    }
}
?>
