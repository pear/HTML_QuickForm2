<?php
/**
 * Class used to test creation of elements via HTML_QuickForm2_Factory::createElement()
 */
class FakeElement
{
    public $name;
    public $data;
    public $label;
    public $attributes;

    public function __construct($name = null, $data = null, $label = null, $attributes = null)
    {
        $this->name         = $name;
        $this->data         = $data;
        $this->label        = $label;
        $this->attributes   = $attributes;
    }
}
?>