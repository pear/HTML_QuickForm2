<?php
/**
 * Rule checking that uploaded file is of the correct MIME type
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
 * Rule checking that uploaded file is of the correct MIME type
 *
 * The Rule needs one configuration parameter for its work: a string with a
 * desired MIME type or array of such strings. This parameter can be passed
 * either to {@link HTML_QuickForm2_Rule::__construct() the Rule constructor}
 * as local configuration or to {@link HTML_QuickForm2_Factory::registerRule()}
 * as global one. As usual, global configuration overrides local one.
 *
 * The Rule considers missing file uploads (UPLOAD_ERR_NO_FILE) valid.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Rule_MimeType extends HTML_QuickForm2_Rule
{
   /**
    * Validates the owner element
    *
    * @return   bool    whether uploaded file's MIME type is correct
    */
    protected function validateOwner()
    {
        $value = $this->owner->getValue();
        if (!isset($value['error']) || UPLOAD_ERR_NO_FILE == $value['error']) {
            return true;
        }
        $mime = $this->getConfig();
        return is_array($mime)? in_array($value['type'], $mime):
                                $value['type'] == $mime;
    }

   /**
    * Sets allowed MIME type(s) for the uploaded file
    *
    * @param string|array $config Allowed MIME type or an array of types
    *
    * @return   HTML_QuickForm2_Rule
    * @throws   HTML_QuickForm2_InvalidArgumentException    if bogus configuration provided
    */
    public function setConfig($config)
    {
        if (!is_string($config) && !is_array($config) || array() === $config) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'MimeType Rule requires MIME type(s), ' .
                preg_replace('/\s+/', ' ', var_export($config, true)) . ' given'
            );
        }
        return parent::setConfig($config);
    }

   /**
    * Sets the element that will be validated by this rule
    *
    * @param HTML_QuickForm2_Node $owner File upload field to validate
    *
    * @throws   HTML_QuickForm2_InvalidArgumentException    if trying to use
    *           this Rule on something that isn't a file upload field
    */
    public function setOwner(HTML_QuickForm2_Node $owner)
    {
        if (!$owner instanceof HTML_QuickForm2_Element_InputFile) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'MimeType Rule can only validate file upload fields, '.
                get_class($owner) . ' given'
            );
        }
        parent::setOwner($owner);
    }
}
?>
