<?php
/**
 * Javascript aggregator and builder class
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2014, Alexey Borzov <avb@php.net>,
 *                          Bertrand Mansion <golgote@mamasam.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Exception classes for HTML_QuickForm2
 */
require_once 'HTML/QuickForm2/Exception.php';

/**
 * Javascript aggregator and builder class
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_JavascriptBuilder
{
   /**
    * Client-side rules
    * @var array
    */
    protected $rules = array();

   /**
    * Elements' setup code
    * @var array
    */
    protected $scripts = array();

   /**
    * Whether to generate a validator object for the form if no rules are present
    *
    * Needed when the form contains an empty repeat element
    *
    * @var array
    */
    protected $forceValidator = array();

    /**
    * Javascript libraries
    * @var array
    */
    protected $libraries = array(
        'base' => array('file' => 'quickform.js')
    );

   /**
    * Default web path to JS library files
    * @var string
    */
    protected $defaultWebPath;

   /**
    * Default filesystem path to JS library files
    * @var string
    */
    protected $defaultAbsPath;

   /**
    * Current form ID
    * @var string
    */
    protected $formId = null;


   /**
    * Constructor, sets default web path to JS library files and default filesystem path
    *
    * @param string $defaultWebPath default web path to JS library files
    *                               (to use in <script src="...">)
    * @param string $defaultAbsPath default filesystem path to JS library files
    *               (to inline these files into the page), this is set to a package
    *                subdirectory of PEAR data_dir if not given
    */
    public function __construct($defaultWebPath = 'js/', $defaultAbsPath = null)
    {
        $this->defaultWebPath = $defaultWebPath;

        if (null === $defaultAbsPath) {
            $defaultAbsPath = '@data_dir@' . DIRECTORY_SEPARATOR . 'HTML_QuickForm2'
                              . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR;
            // package was probably not installed, use relative path
            if (0 === strpos($defaultAbsPath, '@' . 'data_dir@')) {
                $defaultAbsPath = realpath(
                    dirname(dirname(dirname(__FILE__)))
                    . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'js'
                ) . DIRECTORY_SEPARATOR;
            }
        }
        $this->defaultAbsPath = $defaultAbsPath;
    }


   /**
    * Adds a Javascript library file to the list
    *
    * @param string $name     name to reference the library by
    * @param string $fileName file name, without path
    * @param string $webPath  path relative to web root to reference in <script src="">,
    *                         $defaultWebPath will be used if not given
    * @param string $absPath  filesystem path where the file resides, used when inlining
    *                         libraries, $defaultAbsPath will be used if not given
    */
    public function addLibrary($name, $fileName, $webPath = null, $absPath = null)
    {
        $this->libraries[strtolower($name)] = array(
            'file' => $fileName, 'webPath' => $webPath, 'absPath' => $absPath
        );
    }


   /**
    * Returns Javascript libraries
    *
    * @param bool $inline        whether to return a list of library file names
    *                            or contents of files
    * @param bool $addScriptTags whether to enclose the results in <script> tags
    *
    * @return   string|array
    */
    public function getLibraries($inline = false, $addScriptTags = true)
    {
        $ret = $inline? '': array();
        foreach ($this->libraries as $name => $library) {
            if ($inline) {
                $path = !empty($library['absPath'])? $library['absPath']: $this->defaultAbsPath;
                if (DIRECTORY_SEPARATOR != substr($path, -1)) {
                    $path .= DIRECTORY_SEPARATOR;
                }
                if (false === ($file = @file_get_contents($path . $library['file']))) {
                    throw new HTML_QuickForm2_NotFoundException(
                        "File '{$library['file']}' for JS library '{$name}' not found at '{$path}'"
                    );
                }
                $ret .= ('' == $ret? '': "\n") . $file;

            } else {
                $path = !empty($library['webPath'])? $library['webPath']: $this->defaultWebPath;
                if ('/' != substr($path, -1)) {
                    $path .= '/';
                }
                $ret[$name] = $addScriptTags
                              ? "<script type=\"text/javascript\" src=\"{$path}{$library['file']}\"></script>"
                              : $path . $library['file'];
            }
        }
        return ($inline && $addScriptTags) ? $this->wrapScript($ret) : $ret;
    }


   /**
    * Sets ID of the form currently being processed
    *
    * All subsequent calls to addRule() and addElementJavascript() will store
    * the scripts for that form
    *
    * @param string $formId
    */
    public function setFormId($formId)
    {
        $this->formId = $formId;
        $this->rules[$this->formId]          = array();
        $this->scripts[$this->formId]        = array();
        $this->forceValidator[$this->formId] = false;
    }


   /**
    * Adds the Rule javascript to the list of current form Rules
    *
    * @param HTML_QuickForm2_Rule $rule     Rule instance
    * @param bool                 $triggers Whether rule code should contain
    *                                       "triggers" for live validation
    */
    public function addRule(HTML_QuickForm2_Rule $rule, $triggers = false)
    {
        $this->rules[$this->formId][] = $rule->getJavascript($triggers);
    }


   /**
    * Adds element's setup code to form's Javascript
    *
    * @param string $script
    */
    public function addElementJavascript($script)
    {
        $this->scripts[$this->formId][] = $script;
    }


   /**
    * Enables generating a validator for the current form even if no rules are present
    */
    public function forceValidator()
    {
        $this->forceValidator[$this->formId] = true;
    }


   /**
    * Returns per-form javascript (client-side validation and elements' setup)
    *
    * @param string  $formId        form ID, if empty returns code for all forms
    * @param boolean $addScriptTags whether to enclose code in <script> tags
    *
    * @return   string
    */
    public function getFormJavascript($formId = null, $addScriptTags = true)
    {
        $js  = $this->getValidator($formId, false);
        $js .= ('' == $js ? '' : "\n") . $this->getSetupCode($formId, false);
        return $addScriptTags ? $this->wrapScript($js) : $js;
    }


    /**
     * Returns setup code for form elements
     *
     * @param string $formId        form ID, if empty returns code for all forms
     * @param bool   $addScriptTags whether to enclose code in <script> tags
     *
     * @return string
     */
    public function getSetupCode($formId = null, $addScriptTags = false)
    {
        $js = '';
        foreach ($this->scripts as $id => $scripts) {
            if ((null === $formId || $id == $formId) && !empty($scripts)) {
                $js .= ('' == $js? '': "\n") . implode("\n", $scripts);
            }
        }
        return $addScriptTags ? $this->wrapScript($js) : $js;
    }


    /**
     * Returns client-side validation code
     *
     * @param string $formId        form ID, if empty returns code for all forms
     * @param bool   $addScriptTags whether to enclose code in <script> tags
     *
     * @return string
     */
    public function getValidator($formId = null, $addScriptTags = false)
    {
        $js = '';
        foreach ($this->rules as $id => $rules) {
            if ((null === $formId || $id == $formId)
                && (!empty($rules) || !empty($this->forceValidator[$id]))
            ) {
                $js .= ('' == $js ? '' : "\n")
                       . "new qf.Validator(document.getElementById('{$id}'), [\n"
                       . implode(",\n", $rules) . "\n]);";
            }
        }
        return $addScriptTags ? $this->wrapScript($js) : $js;
    }

    /**
     * Wraps the given Javascript code in <script> tags
     *
     * @param string $js Javascript code
     *
     * @return string code wrapped in <script></script> tags,
     *                empty string if $js is empty
     */
    protected function wrapScript($js)
    {
        if ('' != $js) {
            $js = "<script type=\"text/javascript\">\n//<![CDATA[\n"
                  . $js . "\n//]]>\n</script>";
        }
        return $js;
    }

   /**
    * Encodes a value for use as Javascript literal
    *
    * NB: unlike json_encode() we do not enforce UTF-8 charset here
    *
    * @param mixed $value
    *
    * @return   string  value as Javascript literal
    */
    public static function encode($value)
    {
        if (is_null($value)) {
            return 'null';

        } elseif (is_bool($value)) {
            return $value? 'true': 'false';

        } elseif (is_int($value) || is_float($value)) {
            return $value;

        } elseif (is_string($value)) {
            return '"' . strtr($value, array(
                                "\r" => '\r',
                                "\n" => '\n',
                                "\t" => '\t',
                                "'"  => "\\'",
                                '"'  => '\"',
                                '\\' => '\\\\'
                              )) . '"';

        } elseif (is_array($value)) {
            // associative array, encoding as JS object
            if (count($value) && array_keys($value) !== range(0, count($value) - 1)) {
                return '{' . implode(',', array_map(
                    array('HTML_QuickForm2_JavascriptBuilder', 'encodeNameValue'),
                    array_keys($value), array_values($value)
                )) . '}';
            }
            return '[' . implode(',', array_map(
                array('HTML_QuickForm2_JavascriptBuilder', 'encode'),
                $value
            )) . ']';

        } elseif (is_object($value)) {
            $vars = get_object_vars($value);
            return '{' . implode(',', array_map(
                array('HTML_QuickForm2_JavascriptBuilder', 'encodeNameValue'),
                array_keys($vars), array_values($vars)
            )) . '}';

        } else {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Cannot encode ' . gettype($value) . ' as Javascript value'
            );
        }
    }


   /**
    * Callback for array_map used to generate name-value pairs
    *
    * @param mixed $name
    * @param mixed $value
    *
    * @return   string
    */
    protected static function encodeNameValue($name, $value)
    {
        return self::encode((string)$name) . ':' . self::encode($value);
    }
}
?>
