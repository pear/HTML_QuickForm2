<?php
// pear-package-only /**
// pear-package-only  * Base class for HTML_QuickForm2 rules
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Rule.php';

/**
 * Class used to test creation of rules via HTML_QuickForm2_Factory::createRule()
 */
class FakeRule extends HTML_QuickForm2_Rule
{
    public $owner;
    public $registeredType;

    protected function validateOwner() { return true; }
}
?>