<?php
/**
 * Usage example for HTML_QuickForm2 package: Array renderer with Twig template engine
 *
 * For this example to run, you need to install Twig template engine
 * from its separate PEAR channel:
 * <code>
 * $ pear channel-discover pear.twig-project.org
 * $ pear install twig/Twig
 * </code>
 */

// pear-package-only require_once 'HTML/QuickForm2.php';
// pear-package-only require_once 'HTML/QuickForm2/Renderer.php';

if (!class_exists('Twig_Environment', true)) {
    require_once 'Twig/Autoloader.php';
    Twig_Autoloader::register();
}

$options = [
    'a' => 'Letter A', 'b' => 'Letter B', 'c' => 'Letter C',
    'd' => 'Letter D', 'e' => 'Letter E', 'f' => 'Letter F'
];

$main = ["Pop", "Rock", "Classical"];

$secondary = [
    [0 => "Belle & Sebastian", 1 => "Elliot Smith", 2 => "Beck"],
    [3 => "Noir Desir", 4 => "Violent Femmes"],
    [5 => "Wagner", 6 => "Mozart", 7 => "Beethoven"]
];

$form = new HTML_QuickForm2('elements');

// data source with default values:
$form->addDataSource(new HTML_QuickForm2_DataSource_Array([
    'textTest'        => 'Some text',
    'areaTest'        => "Some text\non multiple lines",
    'userTest'        => 'luser',
    'selSingleTest'   => 'f',
    'selMultipleTest' => ['b', 'c'],
    'boxTest'         => '1',
    'radioTest'       => '2',
    'testDate'        => time(),
    'testHierselect'  => [2, 5]
]));

// text input elements
$fsText = $form->addElement('fieldset')->setLabel('Text boxes');
$fsText->addElement(
    'text', 'textTest', ['style' => 'width: 300px;'], ['label' => 'Test Text:']
)->addRule('required', 'This field is required');
$fsText->addElement(
    'password', 'pwdTest', ['style' => 'width: 300px;'], ['label' => 'Test Password:']
);
$area = $fsText->addElement(
    'textarea', 'areaTest', ['style' => 'width: 300px;', 'cols' => 50, 'rows' => 7],
    ['label' => 'Test Textarea:']
);
$area->addRule('required', 'This field is required');

$fsNested = $form->addElement('fieldset')->setLabel('Nested fieldset');
$fsNested->addElement(
    'text', 'userTest', ['style' => 'width: 200px'], ['label' => 'Username:']
);
$fsNested->addElement(
    'password', 'passTest', ['style' => 'width: 200px'], ['label' => 'Password:']
);
// Now we move the fieldset into another fieldset!
$fsText->insertBefore($fsNested, $area);


// selects
$fsSelect = $form->addElement('fieldset')->setLabel('Selects');
$fsSelect->addElement(
    'select', 'selSingleTest', null, ['options' => $options, 'label' => 'Single select:']
);
$fsSelect->addElement(
    'select', 'selMultipleTest', ['multiple' => 'multiple', 'size' => 4],
    ['options' => $options, 'label' => 'Multiple select:']
);

// checkboxes and radios
$fsCheck = $form->addElement('fieldset')->setLabel('Checkboxes and radios');
$fsCheck->addElement(
    'checkbox', 'boxTest', null, ['content' => 'check me', 'label' => 'Test Checkbox:']
);
$fsCheck->addElement(
    'radio', 'radioTest', ['value' => 1], ['content' => 'select radio #1', 'label' => 'Test radio:']
);
$fsCheck->addElement(
    'radio', 'radioTest', ['value' => 2], ['content' => 'select radio #2', 'label' => '(continued)']
);

$fsCustom = $form->addElement('fieldset')->setLabel('Custom elements');
$fsCustom->addElement(
    'date', 'testDate', null,
    ['format' => 'd-F-Y', 'minYear' => date('Y'), 'maxYear' => 2001]
)->setLabel('Today is:');

$fsCustom->addElement('hierselect', 'testHierselect', ['style' => 'width: 20em;'])
         ->setLabel('Hierarchical select:')
         ->loadOptions([$main, $secondary])
         ->setSeparator('<br />');

// buttons
$fsButton = $form->addElement('fieldset')->setLabel('Buttons');
$testReset = $fsButton->addElement(
    'reset', 'testReset', ['value' => 'This is a reset button']
);
$fsButton->addElement(
    'inputbutton', 'testInputButton',
    ['value' => 'Click this button', 'onclick' => "alert('This is a test.');"]
);
$fsButton->addElement(
    'button', 'testButton', ['onclick' => "alert('Almost nothing');", 'type' => 'button'],
    ['content' => '<img src="http://pear.php.net/gifs/pear-icon.gif" '.
          'width="32" height="32" alt="pear" />This button does almost nothing']
);
// submit buttons in nested fieldset
$fsSubmit = $fsButton->addElement('fieldset')->setLabel('These buttons can submit the form');
$fsSubmit->addElement(
    'submit', 'testSubmit', ['value' => 'Test Submit']
);
$fsSubmit->addElement(
    'button', 'testSubmitButton', ['type' => 'submit'],
    ['content' => '<img src="http://pear.php.net/gifs/pear-icon.gif" '.
          'width="32" height="32" alt="pear" />This button submits']
);
$fsSubmit->addElement(
    'image', 'testImage', ['src' => 'http://pear.php.net/gifs/pear-icon.gif']
);

$context = [];
// outputting form values
if ($form->validate()) {
    $context['submitvalues'] = print_r($form->getValue(), true);
    // let's freeze the form and remove the reset button
    $fsButton->removeChild($testReset);
    $form->toggleFrozen(true);
}

if ('@data_dir@' != '@' . 'data_dir@') {
    $filename = '@data_dir@/HTML_QuickForm2/quickform.css';
} else {
    $filename = dirname(dirname(dirname(__DIR__))) . '/data/quickform.css';
}
$context['default_styles'] = file_get_contents($filename);

$renderer = HTML_QuickForm2_Renderer::factory('array');
$form->render($renderer);

$loader   = new Twig_Loader_Filesystem(__DIR__ . '/templates');
// in real life usage you should set up the cache directory!
$twig     = new Twig_Environment($loader);
$template = $twig->loadTemplate('array-twig.tpl');

$template->display($context + [
    'js_libraries' => $renderer->getJavascriptBuilder()->getLibraries(true, true),
    'form'         => $renderer->toArray()
    ]);
?>
