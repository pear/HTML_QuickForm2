<?php
// pear-package-only /**
// pear-package-only  * Base class for <input> elements
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Element/Input.php';

/**
 * Class for <input type="tel" /> elements
 */
class HTML_QuickForm2_Element_InputTel extends HTML_QuickForm2_Element_InputText {
    protected $attributes = ['type' => 'tel'];
}
?>
