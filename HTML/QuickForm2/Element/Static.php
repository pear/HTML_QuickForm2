<?php
/**
 * Class for static elements that only contain text or markup
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
// pear-package-only  * Base class for simple HTML_QuickForm2 elements (not Containers)
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Element.php';

/**
 * Class for static elements that only contain text or markup
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Element_Static extends HTML_QuickForm2_Element
{
   /**
    * Name of the tag to wrap around static element's content
    * @var string|null
    */
    protected $tagName = null;

   /**
    * Whether to output closing tag when $tagName is set and element's content is empty
    * @var bool
    */
    protected $forceClosingTag = true;

   /**
    * Contains options and data used for the element creation
    * - content: Content of the static element
    * @var  array
    */
    protected $data = ['content' => ''];

   /**
    * Class constructor
    *
    * Static element can understand the following keys in $data parameter:
    *   - 'content': content of the static element, e.g. text or markup
    *   - 'tagName': name of the tag to wrap around content, e.g. 'div'.
    *     Using tag names corresponding to form elements will cause an Exception
    *   - 'forceClosingTag': whether to output closing tag in case of empty
    *     content, &lt;foo&gt;&lt;/foo&gt; vs. &lt;foo /&gt;
    *
    * @param string       $name       Element name
    * @param string|array $attributes Attributes (either a string or an array)
    * @param array        $data       Additional element data
    */
    public function __construct($name = null, $attributes = null, array $data = [])
    {
        if (!empty($data['tagName'])) {
            $this->setTagName(
                $data['tagName'],
                !array_key_exists('forceClosingTag', $data) || $data['forceClosingTag']
            );
        }
        unset($data['tagName'], $data['forceClosingTag']);
        parent::__construct($name, $attributes, $data);
    }

   /**
    * Intercepts setting 'name' and 'id' attributes
    *
    * Overrides parent method to allow removal of 'name' attribute on Static
    * elements
    *
    * @param string $name  Attribute name
    * @param string $value Attribute value, null if attribute is being removed
    *
    * @throws   HTML_QuickForm2_InvalidArgumentException    if trying to
    *                                   remove a required attribute
    */
    protected function onAttributeChange($name, $value = null)
    {
        if ('name' === $name && null === $value) {
            unset($this->attributes['name']);
        } else {
            parent::onAttributeChange($name, $value);
        }
    }

   /**
    * Sets the element's name
    *
    * Passing null here will remove the name attribute
    *
    * @param string|null $name
    *
    * @return   $this
    */
    public function setName($name)
    {
        if (null !== $name) {
            return parent::setName($name);
        } else {
            return $this->removeAttribute('name');
        }
    }

    public function getType()
    {
        return 'static';
    }

   /**
    * Static element can not be frozen
    *
    * @param bool $freeze Whether element should be frozen or editable. This
    *                     parameter is ignored in case of static elements
    *
    * @return   bool    Always returns false
    */
    public function toggleFrozen($freeze = null)
    {
        return false;
    }

   /**
    * Sets the contents of the static element
    *
    * @param string|null $content Static content
    *
    * @return $this
    */
    function setContent($content)
    {
        $this->data['content'] = $content;
        return $this;
    }

   /**
    * Returns the contents of the static element
    *
    * @return   string|null
    */
    function getContent()
    {
        return $this->data['content'];
    }

   /**
    * Static element's content can also be set via this method
    *
    * @param mixed $value
    *
    * @return $this
    */
    public function setValue($value)
    {
        $this->setContent($value);
        return $this;
    }

   /**
    * Static elements have no value
    *
    * @return    null
    */
    public function getRawValue()
    {
        return null;
    }

    public function __toString()
    {
        $prefix = $this->getIndent();
        if (null !== ($comment = $this->getComment())) {
            $prefix .= '<!-- ' . $comment . ' -->'
                       . self::getOption(self::OPTION_LINEBREAK) . $this->getIndent();
        }

        if (!$this->tagName) {
            return $prefix . $this->getContent();
        } elseif ('' !== (string)$this->getContent()) {
            return $prefix . '<' . $this->tagName . $this->getAttributes(true)
                   . '>' . $this->getContent() . '</' . $this->tagName . '>';
        } else {
            return $prefix . '<' . $this->tagName . $this->getAttributes(true)
                   . ($this->forceClosingTag ? '></' . $this->tagName . '>' : ' />');
        }
    }

    public function getJavascriptValue($inContainer = false)
    {
        return '';
    }

    public function getJavascriptTriggers()
    {
        return [];
    }

   /**
    * Called when the element needs to update its value from form's data sources
    *
    * Static elements content can be updated with default form values.
    */
    protected function updateValue()
    {
        $name = (string)$this->getName();
        foreach ($this->getDataSources() as $ds) {
            if (!$ds instanceof HTML_QuickForm2_DataSource_Submit
                && (null !== ($value = $ds->getValue($name))
                    || $ds instanceof HTML_QuickForm2_DataSource_NullAware && $ds->hasValue($name))
            ) {
                $this->setContent($value);
                return;
            }
        }
    }

   /**
    * Sets the name of the HTML tag to wrap around static element's content
    *
    * @param string $name         tag name
    * @param bool   $forceClosing whether to output closing tag in case of empty contents
    *
    * @throws HTML_QuickForm2_InvalidArgumentException when trying to set a tag
    *       name corresponding to a form element
    * @return $this
    */
    public function setTagName($name, $forceClosing = true)
    {
        // Prevent people shooting themselves in the proverbial foot
        if (in_array(strtolower($name),
                     ['form', 'fieldset', 'button', 'input', 'select', 'textarea'])
        ) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Do not use tag name '{$name}' with Static element, use proper element class"
            );
        }
        $this->tagName         = (string)$name;
        $this->forceClosingTag = (bool)$forceClosing;

        return $this;
    }
}
?>