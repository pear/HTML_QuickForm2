# HTML_QuickForm2

[![Build Status](https://travis-ci.org/pear/HTML_QuickForm2.svg?branch=trunk)](https://travis-ci.org/pear/HTML_QuickForm2)

This is a repository for [PEAR HTML_QuickForm2] package that has been migrated from [PEAR SVN].

HTML_QuickForm2 provides methods to create, validate and render HTML forms. It is a PHP5 rewrite of [PEAR HTML_QuickForm]
and related packages.

Features:

 * Supports all form elements defined in HTML4, provides several custom elements. Support for HTML5 form elements is
   planned.
 * Server-side and client-side validation, several common rules provided. Validation rules can be combined with
   'and' and 'or' for complex validation scenarios. Client-side validation can be run on changing the form field and
   on submitting the form.
 * Client-side validation and Javascript-backed elements use a small self-contained JS library, there is no
   dependency on JS frameworks.
 * Multipage forms (tabbed forms and wizards) are possible.
 * Pluggable elements, rules, renderers and renderer plugins.

Please report issues via the [PEAR bug tracker] or [Github issues].

Pull requests are welcome.

[PEAR HTML_QuickForm2]: https://pear.php.net/package/HTML_QuickForm2/
[PEAR SVN]: https://svn.php.net/repository/pear/packages/HTML_QuickForm2
[PEAR HTML_QuickForm]: https://pear.php.net/package/HTML_QuickForm/
[PEAR bug tracker]: https://pear.php.net/bugs/search.php?cmd=display&package_name[]=HTML_QuickForm2
[Github issues]: https://github.com/pear/HTML_QuickForm2/issues

## Installation

The package may be installed either with PEAR

    $ pear install HTML_QuickForm2

or with composer

    $ composer require pear/html_quickform2

Since release 2.1 composer installation relies completely on autoloading and does not contain `require_once` calls or 
use `include-path` option.

## Basic usage

```PHP
require_once 'HTML/QuickForm2.php'; // Only when installed with PEAR

$form = new HTML_QuickForm2('tutorial');

// Set defaults for the form elements
$form->addDataSource(new HTML_QuickForm2_DataSource_Array([
    'name' => 'Joe User'
]));

// Add some elements to the form
$fieldset = $form->addElement('fieldset')->setLabel('QuickForm2 tutorial example');
$name = $fieldset->addElement('text', 'name', ['size' => 50, 'maxlength' => 255])
                 ->setLabel('Enter your name:');
$fieldset->addElement('submit', null, ['value' => 'Send!']);

// Define filters and validation rules
$name->addFilter('trim');
$name->addRule('required', 'Please enter your name');

// Try to validate a form
if ($form->validate()) {
    echo '<h1>Hello, ' . htmlspecialchars($name->getValue()) . '!</h1>';
    exit;
}

// Output the form
echo $form;
```

Additional examples are in the docs/examples directory.

## Documentation

...is available on PEAR website

 * [Tutorial](http://pear.php.net/manual/en/package.html.html-quickform2.tutorial.php)
 * [Migration from HTML_QuickForm](http://pear.php.net/manual/en/package.html.html-quickform2.qf-migration.php) and
   [HTML_QuickForm_Controller](http://pear.php.net/manual/en/package.html.html-quickform2.controller-migration.php)
 * [Form elements](http://pear.php.net/manual/en/package.html.html-quickform2.elements.php) overview
 * [Elements' values and validation](http://pear.php.net/manual/en/package.html.html-quickform2.values.php)
 * [Form output customization and Javascript support](http://pear.php.net/manual/en/package.html.html-quickform2.output.php)
 * [Multipage forms](http://pear.php.net/manual/en/package.html.html-quickform2.multipage.php)

[Generated API documentation](http://pear.php.net/package/HTML_QuickForm2/docs/latest/) for the current release is also there.

## Testing, Packaging and Installing (Pear)

To test, run

    $ phpunit tests/

after installing dependencies with composer. You can also test the installed package with

    $ phpunit [PEAR tests dir]/HTML_QuickForm2

Since PEAR package needs its `require_once` statements re-enabled, please run the helper file before packaging and
installing

    $ php pear-package-helper.php

Then to build, simply

    $ pear package .pear-package/package.xml

To install from scratch

    $ pear install .pear-package/package.xml

To upgrade

    $ pear upgrade -f .pear-package/package.xml
