<?php
/**
 * Javascript builder used when rendering a repeat prototype
 *
 * PHP version 5
 *
 * LICENSE
 *
 * This source file is subject to BSD 3-Clause License that is bundled
 * with this package in the file LICENSE and available at the URL
 * https://raw.githubusercontent.com/pear/HTML_QuickForm2/trunk/docs/LICENSE
 *
 * @category  HTML
 * @package   HTML_QuickForm2
 * @author    Alexey Borzov <avb@php.net>
 * @author    Bertrand Mansion <golgote@mamasam.com>
 * @copyright 2006-2022 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

// pear-package-only /** Javascript aggregator and builder class */
// pear-package-only require_once 'HTML/QuickForm2/JavascriptBuilder.php';

/**
 * Javascript builder used when rendering a repeat prototype
 *
 * Instead of returning form setup code and client-side rules as normal
 * Javascript code, it returns them as Javascript string literals. These are
 * expected to be eval()'d when adding a new repeat item.
 *
 * This class is not intended for normal use.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 * @internal
 */
class HTML_QuickForm2_Container_Repeat_JavascriptBuilder
    extends HTML_QuickForm2_JavascriptBuilder
{
    /**
     * Fake "current form" ID
     * @var string
     */
    protected $formId = 'repeat';

    /**
     * Empty list of javascript libraries, base one(s) are in original builder
     * @var array<string, array{file: string, webPath: ?string, absPath: ?string}>
     */
    protected $libraries = [];


    /**
     * Returns rules and element setup code as Javascript string literals
     *
     * @return array array('rules', 'setup code')
     */
    public function getFormJavascriptAsStrings()
    {
        return [
            self::encode(
                empty($this->rules['repeat'])
                ? '' : "[\n" . implode(",\n", $this->rules['repeat']) . "\n]"
            ),
            self::encode(
                empty($this->scripts['repeat'])
                ? '' : implode("\n", $this->scripts['repeat'])
            )
        ];
    }

    /**
     * Passes Javascript libraries added by repeat prototype
     *
     * @param HTML_QuickForm2_JavascriptBuilder $recipient original Javascript builder
     */
    public function passLibraries(HTML_QuickForm2_JavascriptBuilder $recipient)
    {
        foreach ($this->libraries as $name => $library) {
            $recipient->addLibrary(
                $name, $library['file'], $library['webPath'], $library['absPath']
            );
        }
    }
}
?>