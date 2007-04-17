<?php
/**
 * Usage example for HTML_QuickForm2 package: basic elements
 *
 * $Id$
 */ 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
  <head>
    <style type="text/css">
/* styles borrowed from an older release of Tableless Renderer for QF. 
   Newer styles work worse with nested fieldsets */
body {
    margin-left: 10px;
    font-family: Arial,sans-serif;
    font-size: small;
}
form {
    margin: 0;
    padding: 0;
    min-width: 500px;
    max-width: 600px;
    width: 560px;
}
form fieldset {
    border: 1px solid black;
    padding: 10px 5px;
    margin: 0;
    /*width: 560px;*/
}
form fieldset.hidden {
    border: 0;
}
form fieldset legend {
    font-weight: bold;
}
form label {
    margin: 0 0 0 5px;
}
form label.qflabel {
    display: block;
    float: left;
    width: 150px;
    padding: 0;
    margin: 5px 0 0 0;
    text-align: right;
}
form input, form textarea, form select {
    width: auto;
}
form textarea {
    overflow: auto;
}
form br {
    clear: left;
}
form div.qfelement {
    display: inline;
    float: left;
    margin: 5px 0 0 10px;
    padding: 0;
}
form div.qfreqnote {
    font-size: 80%; 
}
form span.error, form span.required {
    color: red;
}
form div.error {
    border: 1px solid red;
    padding: 5px;
}
    </style>
    <title>HTML_QuickForm2 basic elements example</title>
  </head>
  <body>
<?php

$options = array(
    'a' => 'Letter A', 'b' => 'Letter B', 'c' => 'Letter C',
    'd' => 'Letter D', 'e' => 'Letter E', 'f' => 'Letter F'
);

function output_element($element)
{
    if ('fieldset' == $element->getType()) {
        output_fieldset($element);
    } elseif ('hidden' == $element->getType()) {
        echo '<div style="display: none;">' . $element->__toString() . "</div>\n";
    } else {
        echo '<div class="qfrow"><label class="qflabel" for="' . $element->getId() .
             '">' . $element->getLabel() . '</label> <div class="qfelement">' .
             $element->__toString() . "</div></div><br />\n";
    }
}

function output_fieldset($fieldset)
{
    echo '<fieldset' . $fieldset->getAttributes(true) . ">\n<legend>" .
         $fieldset->getLabel() . "</legend>\n";
    foreach ($fieldset as $element) {
        output_element($element);
    }
    echo "</fieldset>\n";
}

require_once 'HTML/QuickForm2.php';

$form = new HTML_QuickForm2('elements');

// data source with default values:
$form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
    'textTest'        => 'Some text',
    'areaTest'        => "Some text\non multiple lines",
    'userTest'        => 'luser',
    'selSingleTest'   => 'f',
    'selMultipleTest' => array('b', 'c'),
    'boxTest'         => '1',
    'radioTest'       => '2'
)));

// text input elements
$fsText = $form->addElement(
    'fieldset', null, null, 'Text boxes'
);
$fsText->addElement(
    'text', 'textTest', null, 'Test Text:', array('style' => 'width: 300px;')
);
$fsText->addElement(
    'password', 'pwdTest', null, 'Test Password:', array('style' => 'width: 300px;')
);
$area = $fsText->addElement(
    'textarea', 'areaTest', null, 'Test Textarea:',
    array('style' => 'width: 300px;', 'cols' => 50, 'rows' => 7)
);

$fsNested = $form->addElement(
    'fieldset', null, null, 'Nested fieldset'
);
$fsNested->addElement(
    'text', 'userTest', null, 'Username:', array('style' => 'width: 200px')
);
$fsNested->addElement(
    'password', 'passTest', null, 'Password:', array('style' => 'width: 200px')
);
// Now we move the fieldset into another fieldset!
$fsText->insertBefore($fsNested, $area);


// selects
$fsSelect = $form->addElement(
    'fieldset', null, null, 'Selects'
);
$fsSelect->addElement(
    'select', 'selSingleTest', array('options' => $options), 'Single select:'
);
$fsSelect->addElement(
    'select', 'selMultipleTest', array('options' => $options), 'Multiple select:',
    array('multiple' => 'multiple', 'size' => 4)
);

// checkboxes and radios
$fsCheck = $form->addElement(
    'fieldset', null, null, 'Checkboxes and radios'
);
$fsCheck->addElement(
    'checkbox', 'boxTest', array('content' => 'check me'), 'Test Checkbox:'
);
$fsCheck->addElement(
    'radio', 'radioTest', array('content' => 'select radio #1'), 'Test radio:', array('value' => 1)
);
$fsCheck->addElement(
    'radio', 'radioTest', array('content' => 'select radio #2'), '(continued)', array('value' => 2)
);

// buttons
$fsButton = $form->addElement(
    'fieldset', null, null, 'Buttons'
);
$testReset = $fsButton->addElement(
    'reset', 'testReset', null, null, array('value' => 'This is a reset button')
);
$fsButton->addElement(
    'inputbutton', 'testInputButton', null, null,
    array('value' => 'Click this button', 'onclick' => "alert('This is a test.');")
);
$fsButton->addElement(
    'button', 'testButton', array('content' => '<img src="http://pear.php.net/gifs/pear-icon.gif" '.
        'width="32" height="32" alt="pear" />This button does almost nothing'),
    null, array('onclick' => "alert('Almost nothing');", 'type' => 'button')
);
// submit buttons in nested fieldset
$fsSubmit = $fsButton->addElement(
    'fieldset', null, null, 'These buttons can submit the form'
);
$fsSubmit->addElement(
    'submit', 'testSubmit', null, null, array('value' => 'Test Submit')
);
$fsSubmit->addElement(
    'button', 'testSubmitButton', array('content' => '<img src="http://pear.php.net/gifs/pear-icon.gif" '.
        'width="32" height="32" alt="pear" />This button submits'),
    null, array('type' => 'submit')
);
$fsSubmit->addElement(
    'image', 'testImage', null, null, array('src' => 'http://pear.php.net/gifs/pear-icon.gif')
);

// outputting form values
if ('POST' == $_SERVER['REQUEST_METHOD']) {
    echo "<pre>\n";
    var_dump($form->getValue());
    echo "</pre>\n<hr />";
    // let's freeze the form and remove the reset button
    $fsButton->removeChild($testReset);
    $form->toggleFrozen(true);
}

echo '<form' . $form->getAttributes(true) . ">\n";
foreach ($form as $element) {
    output_element($element);
}
?>
</form>
  </body>
</html>