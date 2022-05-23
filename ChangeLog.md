# Changes in HTML_QuickForm2

## 2.3.1 - 2022-05-23
* PEAR package of version 2.3.0 was accidentally created with `require_once` statements disabled (see [issue #9])

## 2.3.0 - 2022-04-02
* Upgraded tests, [PHPUnit Polyfills package] is used to run them on PHP 5.6 to PHP 8.1
* Test suite now runs on Github Actions rather than on Travis
* The package runs under PHP 8.1 without `E_DEPRECATED` messages (see [issue #6])
* Use [psalm] for static analysis 
* Minimum required PHP version is now 5.6

## 2.2.2 - 2021-06-25
* Incorrect option name used in `HTML_QuickForm2_Element_Script::__toString()`.
  Thanks to Julian Striegl (striegljulian at gmail dot com) for reporting

## 2.2.1 - 2021-05-09
* Depend on newer HTML_Common2 that does not cause problems with optimized
  composer autoloader, see Github issue #4
* Consistently use `HTML_Common2::OPTION_*` constants for document-wide 
  option names, define and use additional `HTML_QuickForm2_Node::OPTION_*`
  constants instead of directly using `'language'`, `'id_force_append_index'`
  and `'nonce'` strings

## 2.2.0 - 2020-09-14
 * Minimum required PHP version is now 5.4
 * Removed support for magic_quotes_gpc, as `get_magic_quotes_gpc()`
   was deprecated in PHP 7.4 and the functionality itself was disabled 
   since PHP 5.4 (GitHub issue #3)
 * `HTML_QuickForm2_Element_Date::setValue()` will now accept an instance of
   `DateTimeInterface`, not only `DateTime` 


## 2.1.0 - 2019-04-10
 * HTML_QuickForm2 runs under PHP 7.2+ without warnings / &quot;deprecated&quot;
   messages
 * Tests run correctly on PHPUnit versions up to 5
 * It is possible to automatically add `nonce` attributes to inline
   `<script>` tags, just call
   ```HTML_Common2::setOption('nonce', $someNonceValue);```
   
   before outputting the form. This allows running HTML_QuickForm2 with
   reasonable Content-Security-Policy
 * Bundled a separate `LICENSE` file instead of having it in each .php file
   header. Updated phrasing and links to mention 3-Clause BSD license
   the package actually uses.

When installing with composer, files no longer contain `require_once` calls
and `'include-path'` option is not used. The package is now 100% autoloader
compatible, all classes reside in separate files.


## 2.0.2 - 2014-06-28
[Bug #20295] was incorrectly fixed for Static elements, this led to removing
their contents when DataSources did not contain values for them.

## 2.0.1 - 2014-06-26

This is the first release installable with composer, changelogs for older versions 
are available in `package.xml` file or [on PEAR website] 

Bug fixes
 * When using `HTML_QuickForm2_DataSource_Array` and its descendants elements'
   `updateValue()` implementations now differentiate between "no value available
   for an element" and "explicit null value provided for an element"
   (see [bug #20295]). Custom DataSources may implement the new
   `HTML_QuickForm2_DataSource_NullAware` interface to achieve the same.
 * Contents of Static elements added to Repeat are no longer cleared ([bug #19802])
 * Client-side rules for containers within Repeat are correctly removed when
   removing a repeated item ([bug #19803])
 * Client-side validator is always generated for a form with a Repeat having
   some client-side rules on its child elements, even if Repeat is empty
 * Unit tests updated to work with newer PHPUnit, prevent running tests twice
   under some circumstances (see [bug #19038])

Other features and changes
 * Calling `HTML_QuickForm2_Container_Group::setValue()` will clear values of
   those grouped elements that do not have a corresponding key in the passed
   array. Passing a null or an empty array to `setValue()` will clear the values
   of all grouped elements. Previous behaviour was counter-intuitive.
 * Added `HTML_QuickForm2_Element_Select::getOptionContainer()` ([request #19955])
 * `HTML_QuickForm2_Container_Group::setValue()` properly handles a group of radio
   elements ([request #20103])
 * `HTML_QuickForm2_Element_Date::setValue()` can accept an instance of DateTime
 * Extracted `removeErrorMessage()` from `removeRelatedErrors()` of `qf.Validator`
   for easier customizing of client-side errors output


[bug #19038]: https://pear.php.net/bugs/bug.php?id=19038
[bug #19802]: https://pear.php.net/bugs/bug.php?id=19802
[bug #19803]: https://pear.php.net/bugs/bug.php?id=19803
[request #19955]: https://pear.php.net/bugs/bug.php?id=19955
[request #20103]: https://pear.php.net/bugs/bug.php?id=20103
[bug #20295]: https://pear.php.net/bugs/bug.php?id=20295
[on PEAR website]: https://pear.php.net/package/HTML_QuickForm2/download/All
[PHPUnit Polyfills package]: https://github.com/Yoast/PHPUnit-Polyfills
[issue #6]: https://github.com/pear/HTML_QuickForm2/issues/6
[psalm]: https://psalm.dev/
[issue #9]: https://github.com/pear/HTML_QuickForm2/issues/9
