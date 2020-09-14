<?php
/**
 * Rule checking that uploaded file size does not exceed the given limit
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

// pear-package-only /**
// pear-package-only  * Base class for HTML_QuickForm2 rules
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Rule.php';

/**
 * Rule checking that uploaded file size does not exceed the given limit
 *
 * The Rule needs one configuration parameter for its work: the size limit.
 * This limit can be passed either to
 * {@link HTML_QuickForm2_Rule::__construct() the Rule constructor} as local
 * configuration or to {@link HTML_QuickForm2_Factory::registerRule()} as
 * global one. As usual, global configuration overrides local one.
 *
 * Note that if file upload failed due to upload_max_filesize php.ini setting
 * or MAX_FILE_SIZE form field, then this rule won't even be called, due to
 * File element's built-in validation setting the error message.
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
class HTML_QuickForm2_Rule_MaxFileSize extends HTML_QuickForm2_Rule
{
   /**
    * Validates the owner element
    *
    * @return   bool    whether uploaded file's size is within given limit
    */
    protected function validateOwner()
    {
        $value = $this->owner->getValue();
        if (!isset($value['error']) || UPLOAD_ERR_NO_FILE == $value['error']) {
            return true;
        }
        return ($this->getConfig() >= @filesize($value['tmp_name']));
    }

   /**
    * Sets maximum allowed file size
    *
    * @param int $config Maximum allowed size
    *
    * @return   HTML_QuickForm2_Rule
    * @throws   HTML_QuickForm2_InvalidArgumentException    if a bogus size limit was provided
    */
    public function setConfig($config)
    {
        if (0 >= $config) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'MaxFileSize Rule requires a positive size limit, ' .
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
                'MaxFileSize Rule can only validate file upload fields, '.
                get_class($owner) . ' given'
            );
        }
        parent::setOwner($owner);
    }
}
?>
