<?php
// pear-package-only /**
// pear-package-only  * Base class for <input> elements
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Element/Input.php';

/**
 * Class for <input type="range" /> elements
 */
class HTML_QuickForm2_Element_InputRange extends HTML_QuickForm2_Element_InputText {
    protected $attributes = ['type' => 'range'];
}
?>
