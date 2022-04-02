<?php
/**
 * Interface for data sources containing submitted values
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
// pear-package-only require_once 'HTML/QuickForm2/DataSource.php';

/**
 * Interface for data sources containing submitted values
 *
 * This interface provides method for getting information on uploaded files.
 * Additionally some elements will only consider getting their values from data
 * sources implementing this interface.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
interface HTML_QuickForm2_DataSource_Submit extends HTML_QuickForm2_DataSource
{
   /**
    * Returns the information about uploaded file
    *
    * If data source doesn't such information it should return null
    *
    * @param string $name Name of file upload field
    *
    * @return   array|null  Information on uploaded file, from $_FILES array
    */
    public function getUpload($name);
}
?>
