<?php
/**
 * Usage example for HTML_QuickForm2 package: repeat element
 */

// pear-package-only require_once 'HTML/QuickForm2.php';
// pear-package-only require_once 'HTML/QuickForm2/Renderer.php';

$form = new HTML_QuickForm2('testRepeat');

$form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
    'country' => array(4, 706, 180),
    'street'  => array(
        'Secret Taliban caves',
        'Pirate hideout. Aaargh!',
        'Somewhere in the jungle'
    ),
    'default' => array(true, false, false),

    'links' => array(
        'main'  => 'yes_1',
        'title' => array('php.net', 'pear.php.net', 'google.com')
    )
)));

/* @var $fsOne HTML_QuickForm2_Container_Fieldset */
$fsOne    = $form->addFieldset()->setLabel('Fieldset-based repeat element');

/* @var $repeatFs HTML_QuickForm2_Container_Repeat */
$repeatFs = $fsOne->addRepeat()
                  ->setPrototype(HTML_QuickForm2_Factory::createElement('fieldset'))
                  ->setId('repeat-fieldset')
                  ->setLabel('Shipping addresses');

$countries = array(
    ''  => "-- please select --",
    4   => "Afghanistan",
    148 => "Chad",
    180 => "Congo, Democratic Republic of",
    368 => "Iraq",
    706 => "Somalia",
    736 => "Sudan",
    716 => "Zimbabwe"
);

$country = $repeatFs->addSelect('country')->loadOptions($countries)->setLabel('Country:');
$repeatFs->addText('region', array('style' => 'width: 20em;'))->setLabel('Region:');
$street  = $repeatFs->addText('street', array('style' => 'width: 20em;'))->setLabel('Street address:');
$repeatFs->addCheckbox('default')->setContent('default shipping address');
// button to remove a repeated item from a repeat, enabled automatically
$repeatFs->addButton('remove', array('type' => 'button'))
         ->setContent('remove this address')
         ->addClass('repeatRemove');

// setting rules for repeated elements, these will work properly server-side and client-side
$country->addRule('required', 'Please select a country', null,
                  HTML_QuickForm2_Rule::ONBLUR_CLIENT_SERVER);
$street->addRule('required', 'Please input street address', null,
                 HTML_QuickForm2_Rule::ONBLUR_CLIENT_SERVER);


/* @var $fsTwo HTML_QuickForm2_Container_Fieldset */
$fsTwo = $form->addFieldset()->setLabel('Group-based repeat element');
/* @var $repeatGroup HTML_QuickForm2_Container_Repeat */
$repeatGroup = $fsTwo->addRepeat(
                    null, array('id' => 'repeat-group'),
                    array('prototype' => HTML_QuickForm2_Factory::createElement('group', 'links')
                                         ->setLabel('A link:')->setSeparator('&nbsp;'))
               )->setIndexField('links[title]') // not strictly necessary, but doesn't hurt either
                ->setLabel('Links');

$repeatGroup->addText('title', array('style' => 'width: 15em;'));
// specially crafted value attribute to prevent adding index to name
$repeatGroup->addRadio('main', array('value' => 'yes_:idx:'))->setContent('main');
// button to remove a repeated item from a repeat
$repeatGroup->addButton('remove', array('type' => 'button'))
            ->setContent('X')
            ->addClass('repeatRemove');
// a button for adding repeated elements, with an explicit onclick
$fsTwo->addButton('add', array(
    'type'    => 'button',
    'onclick' => "document.getElementById('repeat-group').repeat.add(); return false;"
))->setContent('Add another link');


$form->addSubmit('submit', array('value' => 'Send this form'));

/* @var $renderer HTML_QuickForm2_Renderer_Default */
$renderer = HTML_QuickForm2_Renderer::factory('default');
// a custom template for first repeat element, a link for adding repeated
// elements there will be automatically made active due to repeatAdd class
$renderer->setTemplateForId(
    'repeat-fieldset',
    <<<HTML
<div class="row repeat" id="{id}">
 <qf:label><p>{label}</p></qf:label>
 {content}<br />
 <a class="repeatAdd" href="#">Add another address...</a>
</div>
HTML
);

/* 
// Use this with the callback renderer
$renderer->setCallbackForId(
    'repeat-fieldset', function ($renderer, $repeat) {
        return sprintf(
            '<div class="row repeat" id="%s"><p>%s</p><br /><a class="repeatAdd" href="#">Add another address...</a></div>',
            $repeat->getId(), $repeat->getLabel(), implode(array_pop($renderer->html))
        );
    });
*/

$form->render($renderer);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <style type="text/css">

/* Set up custom font and form width */
body {
    margin-left: 10px;
    font-family: Arial,sans-serif;
    font-size: small;
}

.quickform {
    min-width: 500px;
    max-width: 600px;
    width: 560px;
}

/* Use default styles included with the package */
<?php
if ('@data_dir@' != '@' . 'data_dir@') {
    $filename = '@data_dir@/HTML_QuickForm2/quickform.css';
} else {
    $filename = dirname(dirname(dirname(__FILE__))) . '/data/quickform.css';
}
readfile($filename);
?>

/* http://www.quirksmode.org/css/clearing.html */
#repeat-group .repeatItem { overflow: auto; width: 100%; }

/* zebra table for group-based repeat */
.quickform .repeat .odd  { background-color: #FEE; }
.quickform .repeat .even { background-color: #EEF; }

    </style>
<?php

// Inline QuickForm's javascript libraries
echo $renderer->getJavascriptBuilder()->getLibraries(true, true);

?>
    <title>HTML_QuickForm2 repeat element example</title>
</head>
<body>
<?php

if ($form->validate()) {
    echo "<pre>\n";
    var_dump($form->getValue());
    echo "\n</pre><hr />";
}

echo $renderer;

?>
<script type="text/javascript">
// <![CDATA[

// add event handlers to repeats
var repeatFs    = document.getElementById('repeat-fieldset').repeat,
    repeatGroup = document.getElementById('repeat-group').repeat;

repeatFs.onBeforeAdd = function()
{
    var items = this.getElementsByClass('repeatItem', this.container);
    // 5 visible items and 1 hidden prototype
    if (items.length > 5) {
        alert('5 addresses should be enough for everybody!');
        return false;
    }
    return true;
};

repeatFs.onBeforeRemove = function(item)
{
    var items = this.getElementsByClass('repeatItem', this.container);
    if (2 == items.length) {
        alert('You cannot remove the last address');
        return false;
    }
    return true;
};

repeatGroup.onChange = function()
{
    var items = this.getElementsByClass('repeatItem', this.container);
    for (var i = 1, item; item = items[i]; i++) {
        qf.classes.add(item, i % 2 ? 'odd' : 'even');
        qf.classes.remove(item, i % 2 ? 'even' : 'odd');
    }
};

// paint zebra for initial values
repeatGroup.onChange();

// ]]>
</script>
</body>
</html>