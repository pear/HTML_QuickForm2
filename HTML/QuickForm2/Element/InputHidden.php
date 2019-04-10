<?php
/**
 * Class for <input type="hidden" /> elements
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
// pear-package-only  * Base class for <input> elements
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Element/Input.php';

/**
 * Class for <input type="hidden" /> elements
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Element_InputHidden extends HTML_QuickForm2_Element_Input
{
    protected $attributes = array('type' => 'hidden');

   /**
    * Hidden elements can not be frozen
    *
    * @param bool $freeze Whether element should be frozen or editable. This
    *                     parameter is ignored in case of hidden elements
    *
    * @return   bool    Always returns false
    */
    public function toggleFrozen($freeze = null)
    {
        return false;
    }

    /**
     * Disallows setting an error message on hidden elements
     *
     * @param string|null $error
     *
     * @return HTML_QuickForm2_Element_InputHidden
     * @throws HTML_QuickForm2_InvalidArgumentException if $error is not empty
     */
    public function setError($error = null)
    {
        if (strlen($error)) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Hidden elements cannot have validation errors"
            );
        }
        return parent::setError($error);
    }

    public function render(HTML_QuickForm2_Renderer $renderer)
    {
        $renderer->renderHidden($this);
        $this->renderClientRules($renderer->getJavascriptBuilder());
        return $renderer;
    }
}
?>
