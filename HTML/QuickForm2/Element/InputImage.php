<?php
/**
 * Class for <input type="image" /> elements
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
// pear-package-only  * Base class for <input> elements
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Element/Input.php';

/**
 * Class for <input type="image" /> elements
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Element_InputImage extends HTML_QuickForm2_Element_Input
{
    protected $attributes = ['type' => 'image'];

   /**
    * Coordinates of user click within the image, array contains keys 'x' and 'y'
    * @var  array|null
    */
    protected $coordinates = null;

   /**
    * Image buttons can not be frozen
    *
    * @param bool $freeze Whether element should be frozen or editable. This
    *                     parameter is ignored in case of image elements
    *
    * @return   bool    Always returns false
    */
    public function toggleFrozen($freeze = null)
    {
        return false;
    }

   /**
    * Image button's value cannot be set via this method
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
    * The value is only returned if the form was actually submitted and this
    * image button was clicked. Returns null in all other cases.
    *
    * @return   array|null  An array with keys 'x' and 'y' containing the
    *                       coordinates of user click if the image was clicked,
    *                       null otherwise
    */
    public function getRawValue()
    {
        return $this->getAttribute('disabled')? null: $this->coordinates;
    }

   /**
    * Returns the HTML representation of the element
    *
    * The method changes the element's name to foo[bar][] if it was foo[bar]
    * originally. If it is not done, then one of the click coordinates will be
    * lost, see {@link http://bugs.php.net/bug.php?id=745}
    *
    * @return   string
    */
    public function __toString()
    {
        if (false === strpos($this->attributes['name'], '[')
            || '[]' == substr($this->attributes['name'], -2)
        ) {
            return parent::__toString();
        } else {
            $this->attributes['name'] .= '[]';
            $html = parent::__toString();
            $this->attributes['name']  = substr($this->attributes['name'], 0, -2);
            return $html;
        }
    }

    protected function updateValue()
    {
        foreach ($this->getDataSources() as $ds) {
            if ($ds instanceof HTML_QuickForm2_DataSource_Submit) {
                $name = (string)$this->getName();
                if (false === strpos($name, '[')
                    && null !== ($value = $ds->getValue($name . '_x'))
                ) {
                    $this->coordinates = [
                        'x' => $value,
                        'y' => $ds->getValue($name . '_y')
                    ];
                    return;

                } elseif (false !== strpos($name, '[')) {
                    if ('[]' == substr($name, -2)) {
                        $name = substr($name, 0, -2);
                    }
                    if (null !== ($value = $ds->getValue($name))) {
                        $this->coordinates = [
                            'x' => $value[0],
                            'y' => $value[1]
                        ];
                        return;
                    }
                }
            }
        }
        $this->coordinates = null;
    }
}
?>
