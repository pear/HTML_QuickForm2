<?php
/**
 * Base class for HTML_QuickForm2 rules
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
 * Abstract base class for HTML_QuickForm2 rules
 *
 * This class provides methods that allow chaining several rules together.
 * Its validate() method executes the whole rule chain starting from this rule.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
abstract class HTML_QuickForm2_Rule
{
   /**
    * Constant showing that validation should be run server-side
    * @see  HTML_QuickForm2_Node::addRule()
    */
    const SERVER = 1;

   /**
    * Constant showing that validation should be run client-side (on form submit)
    * @see  HTML_QuickForm2_Node::addRule()
    */
    const CLIENT = 2;

   /**
    * Constant showing that validation should be run client-side (on form submit and on leaving the field)
    * @see  HTML_QuickForm2_Node::addRule()
    */
    const ONBLUR_CLIENT = 6;

   /**
    * A combination of SERVER and CLIENT constants
    * @see  HTML_QuickForm2_Node::addRule()
    */
    const CLIENT_SERVER = 3;

   /**
    * A combination of SERVER and ONBLUR_CLIENT constants
    * @see  HTML_QuickForm2_Node::addRule()
    */
    const ONBLUR_CLIENT_SERVER = 7;

   /**
    * An element whose value will be validated by this rule
    * @var  HTML_QuickForm2_Node
    */
    protected $owner;

   /**
    * An error message to display if validation fails
    * @var  string
    */
    protected $message;

   /**
    * Configuration data for the rule
    * @var  mixed
    */
    protected $config;

   /**
    * Rules chained to this one via "and" and "or" operators
    *
    * The contents can be described as "disjunctive normal form", where an outer
    * array represents a disjunction of conjunctive clauses represented by inner
    * arrays.
    *
    * @var  array
    */
    protected $chainedRules = array(array());


   /**
    * Class constructor
    *
    * @param HTML_QuickForm2_Node $owner   Element to validate
    * @param string               $message Error message to display if validation fails
    * @param mixed                $config  Configuration data for the rule
    */
    public function __construct(HTML_QuickForm2_Node $owner, $message = '', $config = null)
    {
        $this->setOwner($owner);
        $this->setMessage($message);
        $this->setConfig($config);
    }

   /**
    * Merges local configuration with that provided for registerRule()
    *
    * Default behaviour is for global config to override local one, different
    * Rules may implement more complex merging behaviours.
    *
    * @param mixed $localConfig  Local configuration
    * @param mixed $globalConfig Global configuration, usually provided to {@link HTML_QuickForm2_Factory::registerRule()}
    *
    * @return   mixed   Merged configuration
    */
    public static function mergeConfig($localConfig, $globalConfig)
    {
        return is_null($globalConfig)? $localConfig: $globalConfig;
    }

   /**
    * Sets configuration data for the rule
    *
    * @param mixed $config Rule configuration data (specific for a Rule)
    *
    * @return   $this
    * @throws   HTML_QuickForm2_InvalidArgumentException    in case of invalid
    *               configuration data
    */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

   /**
    * Returns the rule's configuration data
    *
    * @return   mixed   Configuration data (specific for a Rule)
    */
    public function getConfig()
    {
        return $this->config;
    }

   /**
    * Sets the error message output by the rule
    *
    * @param string $message Error message to display if validation fails
    *
    * @return $this
    * @throws HTML_QuickForm2_InvalidArgumentException if trying to validate
    *       HTML_QuickForm2_Element_InputHidden with a non-empty error message
    *       (e.g. not in Rule chain)
    */
    public function setMessage($message)
    {
        if ($this->owner instanceof HTML_QuickForm2_Element_InputHidden
            && strlen($message)
        ) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Hidden elements cannot have validation errors"
            );
        }
        $this->message = (string)$message;
        return $this;
    }

   /**
    * Returns the error message output by the rule
    *
    * @return   string  Error message
    */
    public function getMessage()
    {
        return $this->message;
    }

   /**
    * Sets the element that will be validated by this rule
    *
    * @param HTML_QuickForm2_Node $owner Element to validate
    *
    * @throws   HTML_QuickForm2_InvalidArgumentException    if trying to set
    *       an instance of HTML_QuickForm2_Element_Static as rule owner; if
    *       trying to validate HTML_QuickForm2_Element_InputHidden with a
    *       non-empty error message (e.g. not in Rule chain)
    */
    public function setOwner(HTML_QuickForm2_Node $owner)
    {
        // Very little sense to validate static elements as they're, well, static.
        // If someone comes up with a validation rule for these, he can override
        // setOwner() there...
        if ($owner instanceof HTML_QuickForm2_Element_Static) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                get_class($this) . ' cannot validate Static elements'
            );
        }
        if ($owner instanceof HTML_QuickForm2_Element_InputHidden
            && strlen($this->getMessage())
        ) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Hidden elements cannot have validation errors"
            );
        }
        if (null !== $this->owner) {
            $this->owner->removeRule($this);
        }
        $this->owner = $owner;
    }

   /**
    * Adds a rule to the chain with an "and" operator
    *
    * Evaluation is short-circuited, next rule will not be evaluated if the
    * previous one returns false. The method is named this way because "and" is
    * a reserved word in PHP.
    *
    * @param HTML_QuickForm2_Rule $next
    *
    * @return   $this    first rule in the chain (i.e. $this)
    * @throws   HTML_QuickForm2_InvalidArgumentException    when trying to add
    *           a "required" rule to the chain
    */
    public function and_(HTML_QuickForm2_Rule $next)
    {
        if ($next instanceof HTML_QuickForm2_Rule_Required) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'and_(): Cannot add a "required" rule'
            );
        }
        $this->chainedRules[count($this->chainedRules) - 1][] = $next;
        return $this;
    }

   /**
    * Adds a rule to the chain with an "or" operator
    *
    * Evaluation is short-circuited, next rule will not be evaluated if the
    * previous one returns true. The method is named this way because "or" is
    * a reserved word in PHP.
    *
    * @param HTML_QuickForm2_Rule $next
    *
    * @return   $this    first rule in the chain (i.e. $this)
    * @throws   HTML_QuickForm2_InvalidArgumentException    when trying to add
    *           a "required" rule to the chain
    */
    public function or_(HTML_QuickForm2_Rule $next)
    {
        if ($next instanceof HTML_QuickForm2_Rule_Required) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'or_(): Cannot add a "required" rule'
            );
        }
        $this->chainedRules[] = array($next);
        return $this;
    }

   /**
    * Performs validation
    *
    * The whole rule chain is executed. Note that the side effect of this
    * method is setting the error message on element if validation fails
    *
    * @return   boolean     Whether the element is valid
    */
    public function validate()
    {
        $globalValid = false;
        $localValid  = $this->validateOwner();
        foreach ($this->chainedRules as $item) {
            /* @var $multiplier HTML_QuickForm2_Rule */
            foreach ($item as $multiplier) {
                if (!($localValid = $localValid && $multiplier->validate())) {
                    break;
                }
            }
            if ($globalValid = $globalValid || $localValid) {
                break;
            }
            $localValid = true;
        }
        $globalValid or $this->setOwnerError();
        return $globalValid;
    }

   /**
    * Validates the owner element
    *
    * @return   bool    Whether owner element is valid according to the rule
    */
    abstract protected function validateOwner();

   /**
    * Sets the error message on the owner element
    */
    protected function setOwnerError()
    {
        if (strlen($this->getMessage()) && !$this->owner->getError()) {
            $this->owner->setError($this->getMessage());
        }
    }

   /**
    * Returns the client-side validation callback
    *
    * This essentially builds a Javascript version of validateOwner() method,
    * with element ID and Rule configuration hardcoded.
    *
    * @return   string    Javascript function to validate the element's value
    * @throws   HTML_QuickForm2_Exception   if Rule can only be run server-side
    */
    protected function getJavascriptCallback()
    {
        throw new HTML_QuickForm2_Exception(
            get_class($this) . ' does not implement javascript validation'
        );
    }

   /**
    * Returns IDs of form fields that should trigger "live" Javascript validation
    *
    * This returns IDs that are linked to the rule itself.
    *
    * @return array
    */
    protected function getOwnJavascriptTriggers()
    {
        return $this->owner->getJavascriptTriggers();
    }

   /**
    * Returns IDs of form fields that should trigger "live" Javascript validation
    *
    * This returns IDs that are linked to the rule itself and its chained
    * rules. Live validation will be be triggered by 'blur' or 'change' event
    * on any of the elements whose IDs are returned by this method.
    *
    * @return array
    */
    protected function getJavascriptTriggers()
    {
        $triggers = array_flip($this->getOwnJavascriptTriggers());
        foreach ($this->chainedRules as $item) {
            /* @var $multiplier HTML_QuickForm2_Rule */
            foreach ($item as $multiplier) {
                foreach ($multiplier->getJavascriptTriggers() as $trigger) {
                    $triggers[$trigger] = true;
                }
            }
        }
        return array_keys($triggers);
    }

   /**
    * Returns the client-side representation of the Rule
    *
    * This creates an instance of either qf.Rule or qf.LiveRule (depends on
    * $outputTriggers) with initialization parameters:
    *  - callback: {@see getJavascriptCallback()}
    *  - element ID to set error for if validation fails
    *  - error message to set if validation fails
    *  - triggers: {@see getJavascriptTriggers()} (only for
    *    qf.LiveRule when $outputTriggers is true)
    *  - chained rules, array of arrays like in $chainedRules property
    *
    * @param bool $outputTriggers Whether the Rule will be run onblur / onchange
    *
    * @return   string
    * @throws   HTML_QuickForm2_Exception   if Rule or its chained Rules can only
    *                                       be run server-side
    */
    public function getJavascript($outputTriggers = true)
    {
        HTML_QuickForm2_Loader::loadClass('HTML_QuickForm2_JavascriptBuilder');

        $js = $this->getJavascriptCallback() . ",\n\t'" . $this->owner->getId()
              . "', " . HTML_QuickForm2_JavascriptBuilder::encode($this->getMessage());

        $js = $outputTriggers && count($triggers = $this->getJavascriptTriggers())
              ? 'new qf.LiveRule(' . $js . ', ' . HTML_QuickForm2_JavascriptBuilder::encode($triggers)
              : 'new qf.Rule(' . $js;

        if (count($this->chainedRules) > 1 || count($this->chainedRules[0]) > 0) {
            $chained = array();
            foreach ($this->chainedRules as $item) {
                $multipliers = array();
                /* @var $multiplier HTML_QuickForm2_Rule */
                foreach ($item as $multiplier) {
                    $multipliers[] = $multiplier->getJavascript(false);
                }
                $chained[] = '[' . implode(",\n", $multipliers) . ']';
            }
            $js .= ",\n\t [" . implode(",\n", $chained) . "]";
        }
        return $js . ')';
    }
}
?>
