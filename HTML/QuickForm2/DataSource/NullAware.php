<?php
/**
 * Interface for data sources that may contain explicit null values
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
// pear-package-only  * Interface for data sources used by HTML_QuickForm2 objects
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/DataSource.php';

/**
 * Interface for data sources that may contain explicit null values
 *
 * getValue() was designed to return null for missing values, unfortunately that
 * led to bugs when data source contained values explicitly set to null,
 * see {@link http://pear.php.net/bugs/bug.php?id=20295}. This interface defines
 * a method that may be used by elements to check whether a value is available,
 * even if that value is null.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 * @since    Release 2.0.1
 */
interface HTML_QuickForm2_DataSource_NullAware extends HTML_QuickForm2_DataSource
{
    /**
     * Checks whether a value for the element with a given name is available
     *
     * Will return true even if the value is null, similar to array_key_exists()
     *
     * @param string $name Element's name
     *
     * @return bool
     */
    public function hasValue($name);
}
?>