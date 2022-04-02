<?php
/**
 * Data source for HTML_QuickForm2 objects based on superglobal arrays
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
// pear-package-only  * Interface for data sources containing submitted values
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/DataSource/Submit.php';

// pear-package-only /**
// pear-package-only  * Array-based data source for HTML_QuickForm2 objects
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/DataSource/Array.php';

/**
 * Data source for HTML_QuickForm2 objects based on superglobal arrays
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_DataSource_SuperGlobal
    extends HTML_QuickForm2_DataSource_Array
    implements HTML_QuickForm2_DataSource_Submit
{
   /**
    * Information on file uploads (from $_FILES)
    * @var array
    */
    protected $files = [];

   /**
    * Keys present in the $_FILES array
    * @var array
    */
    private static $_fileKeys = ['name', 'type', 'size', 'tmp_name', 'error'];

   /**
    * Class constructor, intializes the internal arrays from superglobals
    *
    * @param string $requestMethod  Request method (GET or POST)
    */
    public function __construct($requestMethod = 'POST')
    {
        if ('GET' == strtoupper($requestMethod)) {
            parent::__construct($_GET);
        } else {
            parent::__construct($_POST);
            $this->files = $_FILES;
        }
    }

   /**
    * A recursive version of array_map() function
    *
    * @param callable $callback Callback function to apply
    * @param mixed    $arr      Input array
    *
    * @return    array with callback applied
    */
    protected function arrayMapRecursive($callback, $arr)
    {
        if (!is_array($arr)) {
            return call_user_func($callback, $arr);
        }
        $mapped = [];
        foreach ($arr as $k => $v) {
            $mapped[$k] = is_array($v)?
                          $this->arrayMapRecursive($callback, $v):
                          call_user_func($callback, $v);
        }
        return $mapped;
    }

    public function getUpload($name)
    {
        if (empty($this->files)) {
            return null;
        }
        if (false !== ($pos = strpos($name, '['))) {
            $tokens = explode('[', str_replace(']', '', $name));
            $base   = array_shift($tokens);
            $value  = [];
            if (!isset($this->files[$base]['name'])) {
                return null;
            }
            foreach (self::$_fileKeys as $key) {
                $value[$key] = $this->files[$base][$key];
            }

            do {
                $token = array_shift($tokens);
                if (!isset($value['name'][$token])) {
                    return null;
                }
                foreach (self::$_fileKeys as $key) {
                    $value[$key] = $value[$key][$token];
                }
            } while (!empty($tokens));
            return $value;
        } elseif (isset($this->files[$name])) {
            return $this->files[$name];
        } else {
            return null;
        }
    }
}
?>
