<?php
/**
 * Class used to test creation of elements via HTML_QuickForm2_Factory::createElement()
 */
class FakeElement
{
    public $name;
    public $options;
    public $label;
    public $attributes;

    public function __construct($name = null, $options = null, $label = null, $attributes = null)
    {
        $this->name         = $name;
        $this->options      = $options;
        $this->label        = $label;
        $this->attributes   = $attributes;
    }
}
?>