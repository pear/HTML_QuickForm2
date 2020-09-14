<?php
/**
 * Class presenting the values stored in session by Controller as submitted ones
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
 * @copyright 2006-2020 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

// pear-package-only /** Interface for data sources containing submitted values */
// pear-package-only require_once 'HTML/QuickForm2/DataSource/Submit.php';

// pear-package-only /** Array-based data source for HTML_QuickForm2 objects */
// pear-package-only require_once 'HTML/QuickForm2/DataSource/Array.php';

/**
 * Class presenting the values stored in session by Controller as submitted ones
 *
 * This is a less hackish implementation of loadValues() method in old
 * HTML_QuickForm_Controller. The values need to be presented as submitted so
 * that elements like checkboxes and multiselects do not try to use default
 * values from subsequent datasources.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_DataSource_Session
    extends HTML_QuickForm2_DataSource_Array
    implements HTML_QuickForm2_DataSource_Submit
{
   /**
    * File upload data is not stored in the session
    *
    * @param string $name Ignored
    *
    * @return null
    */
    public function getUpload($name)
    {
        return null;
    }
}
?>
