<?php
/**
 * Array-based data source for HTML_QuickForm2 objects
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
// pear-package-only  * Interface for data sources used by HTML_QuickForm2 objects
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/DataSource/NullAware.php';

/**
 * Array-based data source for HTML_QuickForm2 objects
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_DataSource_Array implements HTML_QuickForm2_DataSource_NullAware
{
   /**
    * Array containing elements' values
    * @var array
    */
    protected $values;

   /**
    * Class constructor, initializes the values array
    *
    * @param array $values Array containing the elements' values
    */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public function getValue($name)
    {
        if (empty($this->values)) {
            return null;
        }
        if (strpos($name, '[')) {
            $tokens = explode('[', str_replace(']', '', $name));
            $value = $this->values;
            do {
                $token = array_shift($tokens);
                if (!is_array($value) || !isset($value[$token])) {
                    return null;
                }
                $value = $value[$token];
            } while (!empty($tokens));
            return $value;
        } elseif (isset($this->values[$name])) {
            return $this->values[$name];
        } else {
            return null;
        }
    }

    public function hasValue($name)
    {
        if (empty($this->values)) {
            return false;

        } elseif (!strpos($name, '[')) {
            return array_key_exists($name, $this->values);

        } else {
            $tokens = explode('[', str_replace(']', '', $name));
            $value  = $this->values;
            do {
                $token = array_shift($tokens);
                if (!is_array($value) || !array_key_exists($token, $value)) {
                    return false;
                }
                $value = $value[$token];
            } while (!empty($tokens));
            return true;
        }
    }
}
?>
