<?php
/**
 * Class for <textarea> elements
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
 * Class for <textarea> elements
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Element_Textarea extends HTML_QuickForm2_Element
{
    protected $persistent = true;

   /**
    * Value for textarea field
    * @var  string|null
    */
    protected $value = null;

    public function getType()
    {
        return 'textarea';
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getRawValue()
    {
        return empty($this->attributes['disabled'])? $this->value: null;
    }

    public function __toString()
    {
        if ($this->frozen) {
            return $this->getFrozenHtml();
        } else {
            return $this->getIndent()
                   . '<textarea' . $this->getAttributes(true) . '>'
                   . preg_replace(
                       "/(\r\n|\n|\r)/",
                       '&#010;',
                       htmlspecialchars((string)$this->value, ENT_QUOTES, self::getOption(self::OPTION_CHARSET))
                   )
                   . '</textarea>';
        }
    }

    public function getFrozenHtml()
    {
        $value = htmlspecialchars((string)$this->value, ENT_QUOTES, self::getOption(self::OPTION_CHARSET));
        if ('off' == $this->getAttribute('wrap')) {
            $html = $this->getIndent() . '<pre>' . $value .
                    '</pre>' . self::getOption(self::OPTION_LINEBREAK);
        } else {
            $html = nl2br($value) . self::getOption(self::OPTION_LINEBREAK);
        }
        return $html . $this->getPersistentContent();
    }
}
?>
