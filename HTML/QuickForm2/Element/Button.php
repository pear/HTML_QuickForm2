<?php
/**
 * Class for <button> elements
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
// pear-package-only  * Base class for simple HTML_QuickForm2 elements
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Element.php';

/**
 * Class for <button> elements
 *
 * Note that this element was named 'xbutton' in previous version of QuickForm,
 * the name 'button' being used for current 'inputbutton' element.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Element_Button extends HTML_QuickForm2_Element
{
   /**
    * Contains options and data used for the element creation
    * - content: Content to be displayed between <button></button> tags
    * @var  array
    */
    protected $data = ['content' => ''];

   /**
    * Element's submit value
    * @var  string|null
    */
    protected $submitValue = null;


    public function getType()
    {
        return 'button';
    }

   /**
    * Buttons can not be frozen
    *
    * @param bool $freeze Whether element should be frozen or editable. This
    *                     parameter is ignored in case of buttons
    *
    * @return   bool    Always returns false
    */
    public function toggleFrozen($freeze = null)
    {
        return false;
    }

   /**
    * Sets the contents of the button element
    *
    * @param string $content Button content (HTML to add between <button></button> tags)
    *
    * @return $this
    */
    function setContent($content)
    {
        $this->data['content'] = $content;
        return $this;
    }

   /**
    * Button's value cannot be set via this method
    *
    * @param mixed $value Element's value, this parameter is ignored
    *
    * @return $this
    */
    public function setValue($value)
    {
        return $this;
    }

   /**
    * Returns the element's value
    *
    * The value is only returned if the following is true
    *  - button has 'type' attribute set to 'submit' (or no 'type' attribute)
    *  - the form was submitted by clicking on this button
    *
    * This method returns the actual value submitted by the browser. Note that
    * different browsers submit different values!
    *
    * @return    string|null
    */
    public function getRawValue()
    {
        if ((empty($this->attributes['type']) || 'submit' == $this->attributes['type'])
            && !$this->getAttribute('disabled')
        ) {
            return $this->submitValue;
        } else {
            return null;
        }
    }

    public function __toString()
    {
        return $this->getIndent() . '<button' . $this->getAttributes(true) .
               '>' . $this->data['content'] . '</button>';
    }

    protected function updateValue()
    {
        foreach ($this->getDataSources() as $ds) {
            if ($ds instanceof HTML_QuickForm2_DataSource_Submit
                && null !== ($value = $ds->getValue((string)$this->getName()))
            ) {
                $this->submitValue = $value;
                return;
            }
        }
        $this->submitValue = null;
    }
}
?>
