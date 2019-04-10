<?php
/**
 * Class for <input type="checkbox" /> elements
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
// pear-package-only  * Base class for checkboxes and radios
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Element/InputCheckable.php';

/**
 * Class for <input type="checkbox" /> elements
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Element_InputCheckbox extends HTML_QuickForm2_Element_InputCheckable
{
    protected $attributes = array('type' => 'checkbox');

    protected $frozenHtml = array(
        'checked'   => '<code>[x]</code>',
        'unchecked' => '<code>[&nbsp;]</code>'
    );

    public function __construct($name = null, $attributes = null, array $data = array())
    {
        parent::__construct($name, $attributes, $data);
        if (null === $this->getAttribute('value')) {
            $this->setAttribute('value', 1);
        }
    }

    protected function updateValue()
    {
        $name = $this->getName();
        if ('[]' == substr($name, -2)) {
            $name = substr($name, 0, -2);
        }
        foreach ($this->getDataSources() as $ds) {
            if (null !== ($value = $ds->getValue($name))
                || $ds instanceof HTML_QuickForm2_DataSource_Submit
                || ($ds instanceof HTML_QuickForm2_DataSource_NullAware && $ds->hasValue($name))
            ) {
                if (!is_array($value)) {
                    $this->setValue($value);
                } elseif (in_array($this->getAttribute('value'), array_map('strval', $value), true)) {
                    $this->setAttribute('checked');
                } else {
                    $this->removeAttribute('checked');
                }
                return;
            }
        }
        // if *some* data sources were searched and we did not find a value -> uncheck the box
        if (!empty($ds)) {
            $this->removeAttribute('checked');
        }
    }
}
?>
