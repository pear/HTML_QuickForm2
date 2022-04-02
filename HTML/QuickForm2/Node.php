<?php
/**
 * Base class for all HTML_QuickForm2 elements
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

// pear-package-only /**
// pear-package-only  * HTML_Common2 - base class for HTML elements
// pear-package-only  */
// pear-package-only require_once 'HTML/Common2.php';

// pear-package-only /**
// pear-package-only  * Exception classes for HTML_QuickForm2
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Exception.php';

// pear-package-only /**
// pear-package-only  * Static factory class for QuickForm2 elements
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Factory.php';

// pear-package-only /**
// pear-package-only  * Base class for HTML_QuickForm2 rules
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Rule.php';


/**
 * Abstract base class for all QuickForm2 Elements and Containers
 *
 * This class is mostly here to define the interface that should be implemented
 * by the subclasses. It also contains static methods handling generation
 * of unique ids for elements which do not have ids explicitly set.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
abstract class HTML_QuickForm2_Node extends HTML_Common2
{
    /**
     * Name of option containing default language for various elements' messages
     */
    const OPTION_LANGUAGE = 'language';

    /**
     * Name of option that toggles always appending a numeric index to generated id values
     *
     * By default, we generate element IDs with numeric indexes appended even for
     * elements with unique names. If you want IDs to be equal to the element
     * names by default, set this configuration option to false.
     */
    const OPTION_ID_FORCE_APPEND_INDEX = 'id_force_append_index';

    /**
     * Name of option containing a value for "nonce" attribute of generated &lt;script&gt; tags
     */
    const OPTION_NONCE = 'nonce';

   /**
    * Array containing the parts of element ids
    * @var array
    */
    protected static $ids = [];

   /**
    * Element's "frozen" status
    * @var boolean
    */
    protected $frozen = false;

   /**
    * Whether element's value should persist when element is frozen
    * @var boolean
    */
    protected $persistent = false;

   /**
    * Element containing current
    * @var HTML_QuickForm2_Container|null
    */
    protected $container = null;

   /**
    * Contains options and data used for the element creation
    * @var  array
    */
    protected $data = [];

   /**
    * Validation rules for element
    * @var  array<int, array{HTML_QuickForm2_Rule, int}>
    */
    protected $rules = [];

   /**
    * An array of callback filters for element
    * @var  array<int, array{callable, array}>
    */
    protected $filters = [];

   /**
    * Recursive filter callbacks for element
    *
    * These are recursively applied for array values of element or propagated
    * to contained elements if the element is a Container
    *
    * @var  array<int, array{callable, array}>
    */
    protected $recursiveFilters = [];

   /**
    * Error message (usually set via Rule if validation fails)
    * @var  string
    */
    protected $error = '';

   /**
    * Changing 'name' and 'id' attributes requires some special handling
    * @var string[]
    */
    protected $watchedAttributes = ['id', 'name'];

   /**
    * Intercepts setting 'name' and 'id' attributes
    *
    * These attributes should always be present and thus trying to remove them
    * will result in an exception. Changing their values is delegated to
    * setName() and setId() methods, respectively
    *
    * @param string $name  Attribute name
    * @param string $value Attribute value, null if attribute is being removed
    *
    * @throws   HTML_QuickForm2_InvalidArgumentException    if trying to
    *                                   remove a required attribute
    */
    protected function onAttributeChange($name, $value = null)
    {
        if ('name' == $name) {
            if (null === $value) {
                throw new HTML_QuickForm2_InvalidArgumentException(
                    "Required attribute 'name' can not be removed"
                );
            } else {
                $this->setName($value);
            }
        } elseif ('id' == $name) {
            if (null === $value) {
                throw new HTML_QuickForm2_InvalidArgumentException(
                    "Required attribute 'id' can not be removed"
                );
            } else {
                $this->setId($value);
            }
        }
    }

   /**
    * Class constructor
    *
    * @param string       $name       Element name
    * @param string|array $attributes HTML attributes (either a string or an array)
    * @param array        $data       Element data (label, options used for element setup)
    */
    public function __construct($name = null, $attributes = null, array $data = [])
    {
        parent::__construct($attributes);
        $this->setName($name);
        // Autogenerating the id if not set on previous steps
        if ('' === (string)$this->getId()) {
            $this->setId();
        }
        if (!empty($data)) {
            $this->data = array_merge($this->data, $data);
        }
    }


   /**
    * Generates an id for the element
    *
    * Called when an element is created without explicitly given id
    *
    * @param string $elementName Element name
    *
    * @return string The generated element id
    */
    protected static function generateId($elementName)
    {
        $stop      =  !self::getOption(self::OPTION_ID_FORCE_APPEND_INDEX);
        $tokens    =  '' !== $elementName
                      ? explode('[', str_replace(']', '', $elementName))
                      : ($stop? ['qfauto', ''] : ['qfauto']);
        $container =& self::$ids;
        $id        =  '';

        do {
            $token = array_shift($tokens);
            // prevent generated ids starting with numbers
            if ('' == $id && is_numeric($token)) {
                $token = 'qf' . $token;
            }
            // Handle the 'array[]' names
            if ('' === $token) {
                if (empty($container)) {
                    $token = 0;
                } else {
                    $keys  = array_filter(array_keys($container), 'is_numeric');
                    $token = empty($keys) ? 0 : end($keys);
                    while (isset($container[$token])) {
                        $token++;
                    }
                }
            }
            $id .= '-' . $token;
            if (!isset($container[$token])) {
                $container[$token] = [];
            // Handle duplicate names when not having mandatory indexes
            } elseif (empty($tokens) && $stop) {
                $tokens[] = '';
            }
            // Handle mandatory indexes
            if (empty($tokens) && !$stop) {
                $tokens[] = '';
                $stop     = true;
            }
            $container =& $container[$token];
        } while (!empty($tokens));

        return substr($id, 1);
    }


   /**
    * Stores the explicitly given id to prevent duplicate id generation
    *
    * @param string $id Element id
    */
    protected static function storeId($id)
    {
        $tokens    =  explode('-', $id);
        $container =& self::$ids;

        do {
            $token = array_shift($tokens);
            if (!isset($container[$token])) {
                $container[$token] = [];
            }
            $container =& $container[$token];
        } while (!empty($tokens));
    }


   /**
    * Returns the element options
    *
    * @return   array
    */
    public function getData()
    {
        return $this->data;
    }


   /**
    * Returns the element's type
    *
    * @return   string
    */
    abstract public function getType();


   /**
    * Returns the element's name
    *
    * @return   string|null
    */
    public function getName()
    {
        return isset($this->attributes['name'])? $this->attributes['name']: null;
    }


   /**
    * Sets the element's name
    *
    * @param string|null $name
    *
    * @return $this
    */
    abstract public function setName($name);


   /**
    * Returns the element's id
    *
    * @return   string|null
    */
    public function getId()
    {
        return isset($this->attributes['id'])? $this->attributes['id']: null;
    }


   /**
    * Sets the element's id
    *
    * Please note that elements should always have an id in QuickForm2 and
    * therefore it will not be possible to remove the element's id or set it to
    * an empty value. If id is not explicitly given, it will be autogenerated.
    *
    * @param string|null $id Element's id, will be autogenerated if not given
    *
    * @return   $this
    * @throws   HTML_QuickForm2_InvalidArgumentException if id contains invalid
    *           characters (i.e. spaces)
    */
    public function setId($id = null)
    {
        if (is_null($id)) {
            $id = self::generateId((string)$this->getName());
        // HTML5 specification only disallows having space characters in id,
        // so we don't do stricter checks here
        } elseif (strpbrk($id, " \r\n\t\x0C")) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "The value of 'id' attribute should not contain space characters"
            );
        } else {
            self::storeId($id);
        }
        $this->attributes['id'] = (string)$id;
        return $this;
    }


   /**
    * Returns the element's value without filters applied
    *
    * @return   mixed
    */
    abstract public function getRawValue();

   /**
    * Returns the element's value, possibly with filters applied
    *
    * @return mixed
    */
    public function getValue()
    {
        $value = $this->getRawValue();
        return is_null($value)? null: $this->applyFilters($value);
    }

   /**
    * Sets the element's value
    *
    * @param mixed $value
    *
    * @return $this
    */
    abstract public function setValue($value);


   /**
    * Returns the element's label(s)
    *
    * @return   string|string[]|null
    */
    public function getLabel()
    {
        if (isset($this->data['label'])) {
            return $this->data['label'];
        }
        return null;
    }


   /**
    * Sets the element's label(s)
    *
    * @param string|string[]|null $label Label for the element (may be an array of labels)
    *
    * @return $this
    */
    public function setLabel($label)
    {
        $this->data['label'] = $label;
        return $this;
    }


   /**
    * Changes the element's frozen status
    *
    * @param bool $freeze Whether the element should be frozen or editable. If
    *                     omitted, the method will not change the frozen status,
    *                     just return its current value
    *
    * @return   bool    Old value of element's frozen status
    */
    public function toggleFrozen($freeze = null)
    {
        $old = $this->frozen;
        if (null !== $freeze) {
            $this->frozen = (bool)$freeze;
        }
        return $old;
    }


   /**
    * Changes the element's persistent freeze behaviour
    *
    * If persistent freeze is on, the element's value will be kept (and
    * submitted) in a hidden field when the element is frozen.
    *
    * @param bool $persistent New value for "persistent freeze". If omitted, the
    *                         method will not set anything, just return the current
    *                         value of the flag.
    *
    * @return   bool    Old value of "persistent freeze" flag
    */
    public function persistentFreeze($persistent = null)
    {
        $old = $this->persistent;
        if (null !== $persistent) {
            $this->persistent = (bool)$persistent;
        }
        return $old;
    }


   /**
    * Adds the link to the element containing current
    *
    * @param HTML_QuickForm2_Container $container Element containing
    *                           the current one, null if the link should
    *                           really be removed (if removing from container)
    *
    * @throws   HTML_QuickForm2_InvalidArgumentException   If trying to set a
    *                               child of an element as its container
    */
    protected function setContainer(HTML_QuickForm2_Container $container = null)
    {
        if (null !== $container) {
            $check = $container;
            do {
                if ($this === $check) {
                    throw new HTML_QuickForm2_InvalidArgumentException(
                        'Cannot set an element or its child as its own container'
                    );
                }
            } while ($check = $check->getContainer());
            if (null !== $this->container && $container !== $this->container) {
                $this->container->removeChild($this);
            }
        }
        $this->container = $container;
        if (null !== $container) {
            $this->updateValue();
        }
    }


   /**
    * Returns the element containing current
    *
    * @return   HTML_QuickForm2_Container|null
    */
    public function getContainer()
    {
        return $this->container;
    }

   /**
    * Returns the data sources for this element
    *
    * @return   array
    */
    protected function getDataSources()
    {
        if (empty($this->container)) {
            return [];
        } else {
            return $this->container->getDataSources();
        }
    }

   /**
    * Called when the element needs to update its value from form's data sources
    */
    abstract protected function updateValue();

   /**
    * Adds a validation rule
    *
    * @param HTML_QuickForm2_Rule|string $rule           Validation rule or rule type
    * @param string|int                  $messageOrRunAt If first parameter is rule type,
    *            then message to display if validation fails, otherwise constant showing
    *            whether to perfom validation client-side and/or server-side
    * @param mixed                       $options        Configuration data for the rule
    * @param int                         $runAt          Whether to perfom validation
    *               server-side and/or client side. Combination of
    *               HTML_QuickForm2_Rule::SERVER and HTML_QuickForm2_Rule::CLIENT constants
    *
    * @return   HTML_QuickForm2_Rule            The added rule
    * @throws   HTML_QuickForm2_InvalidArgumentException    if $rule is of a
    *               wrong type or rule name isn't registered with Factory
    * @throws   HTML_QuickForm2_NotFoundException   if class for a given rule
    *               name cannot be found
    */
    public function addRule(
        $rule, $messageOrRunAt = '', $options = null,
        $runAt = HTML_QuickForm2_Rule::SERVER
    ) {
        if ($rule instanceof HTML_QuickForm2_Rule) {
            $rule->setOwner($this);
            $runAt = is_int($messageOrRunAt) ? $messageOrRunAt : HTML_QuickForm2_Rule::SERVER;
        } elseif (is_string($rule)) {
            $rule = HTML_QuickForm2_Factory::createRule($rule, $this, (string)$messageOrRunAt, $options);
        } else {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'addRule() expects either a rule type or ' .
                'a HTML_QuickForm2_Rule instance'
            );
        }

        $this->rules[] = [$rule, $runAt];
        return $rule;
    }

   /**
    * Removes a validation rule
    *
    * The method will *not* throw an Exception if the rule wasn't added to the
    * element.
    *
    * @param HTML_QuickForm2_Rule $rule Validation rule to remove
    *
    * @return   HTML_QuickForm2_Rule    Removed rule
    */
    public function removeRule(HTML_QuickForm2_Rule $rule)
    {
        foreach ($this->rules as $i => $r) {
            if ($r[0] === $rule) {
                unset($this->rules[$i]);
                break;
            }
        }
        return $rule;
    }

   /**
    * Creates a validation rule
    *
    * This method is mostly useful when when chaining several rules together
    * via {@link HTML_QuickForm2_Rule::and_()} and {@link HTML_QuickForm2_Rule::or_()}
    * methods:
    * <code>
    * $first->addRule('nonempty', 'Fill in either first or second field')
    *     ->or_($second->createRule('nonempty'));
    * </code>
    *
    * @param string $type    Rule type
    * @param string $message Message to display if validation fails
    * @param mixed  $options Configuration data for the rule
    *
    * @return   HTML_QuickForm2_Rule    The created rule
    * @throws   HTML_QuickForm2_InvalidArgumentException If rule type is unknown
    * @throws   HTML_QuickForm2_NotFoundException        If class for the rule
    *           can't be found and/or loaded from file
    */
    public function createRule($type, $message = '', $options = null)
    {
        return HTML_QuickForm2_Factory::createRule($type, $this, $message, $options);
    }


   /**
    * Checks whether an element is required
    *
    * @return   boolean
    */
    public function isRequired()
    {
        foreach ($this->rules as $rule) {
            if ($rule[0] instanceof HTML_QuickForm2_Rule_Required) {
                return true;
            }
        }
        return false;
    }

   /**
    * Adds element's client-side validation rules to a builder object
    *
    * @param HTML_QuickForm2_JavascriptBuilder $builder
    */
    protected function renderClientRules(HTML_QuickForm2_JavascriptBuilder $builder)
    {
        if ($this->toggleFrozen()) {
            return;
        }
        $onblur = HTML_QuickForm2_Rule::ONBLUR_CLIENT ^ HTML_QuickForm2_Rule::CLIENT;
        foreach ($this->rules as $rule) {
            if (0 !== ($rule[1] & HTML_QuickForm2_Rule::CLIENT)) {
                $builder->addRule($rule[0], 0 !== ($rule[1] & $onblur));
            }
        }
    }

   /**
    * Performs the server-side validation
    *
    * @return   boolean     Whether the element is valid
    */
    protected function validate()
    {
        foreach ($this->rules as $rule) {
            if ('' !== $this->error) {
                return false;
            }
            if (0 !== ($rule[1] & HTML_QuickForm2_Rule::SERVER)) {
                $rule[0]->validate();
            }
        }
        return '' === $this->error;
    }

   /**
    * Sets the error message to the element
    *
    * @param string $error
    *
    * @return $this
    */
    public function setError($error = null)
    {
        $this->error = (string)$error;
        return $this;
    }

   /**
    * Returns the error message for the element
    *
    * @return   string
    */
    public function getError()
    {
        return $this->error;
    }

   /**
    * Returns Javascript code for getting the element's value
    *
    * @param bool $inContainer Whether it should return a parameter for
    *                          qf.form.getContainerValue()
    *
    * @return string
    */
    abstract public function getJavascriptValue($inContainer = false);

   /**
    * Returns IDs of form fields that should trigger "live" Javascript validation
    *
    * Rules added to this element with parameter HTML_QuickForm2_Rule::ONBLUR_CLIENT
    * will be run by after these form elements change or lose focus
    *
    * @return array
    */
    abstract public function getJavascriptTriggers();

    /**
     * Adds a filter
     *
     * A filter is simply a PHP callback which will be applied to the element value
     * when getValue() is called.
     *
     * @param callable $callback The PHP callback used for filter
     * @param array    $options  Optional arguments for the callback. The first parameter
     *                       will always be the element value, then these options will
     *                       be used as parameters for the callback.
     *
     * @return   $this    The element
     * @throws   HTML_QuickForm2_InvalidArgumentException    If callback is incorrect
     */
    public function addFilter($callback, array $options = [])
    {
        if (!is_callable($callback, false, $callbackName)) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Filter should be a valid callback, '{$callbackName}' was given"
            );
        }
        $this->filters[] = [$callback, $options];
        return $this;
    }

    /**
     * Adds a recursive filter
     *
     * A filter is simply a PHP callback which will be applied to the element value
     * when getValue() is called. If the element value is an array, for example with
     * selects of type 'multiple', the filter is applied to all values recursively.
     * A filter on a container will not be applied on a container value but
     * propagated to all contained elements instead.
     *
     * If the element is not a container and its value is not an array the behaviour
     * will be identical to filters added via addFilter().
     *
     * @param callable $callback The PHP callback used for filter
     * @param array    $options  Optional arguments for the callback. The first parameter
     *                       will always be the element value, then these options will
     *                       be used as parameters for the callback.
     *
     * @return   $this    The element
     * @throws   HTML_QuickForm2_InvalidArgumentException    If callback is incorrect
     */
    public function addRecursiveFilter($callback, array $options = [])
    {
        if (!is_callable($callback, false, $callbackName)) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Filter should be a valid callback, '{$callbackName}' was given"
            );
        }
        $this->recursiveFilters[] = [$callback, $options];
        return $this;
    }

   /**
    * Helper function for applying filter callback to a value
    *
    * @param mixed &$value Value being filtered
    * @param mixed $key    Array key (not used, present to be able to use this
    *                      method as a callback to array_walk_recursive())
    * @param array $filter Array containing callback and additional callback
    *                      parameters
    */
    protected static function applyFilter(&$value, $key, $filter)
    {
        list($callback, $options) = $filter;
        array_unshift($options, $value);
        $value = call_user_func_array($callback, $options);
    }

    /**
     * Applies non-recursive filters on element value
     *
     * @param mixed $value Element value
     *
     * @return   mixed   Filtered value
     */
    protected function applyFilters($value)
    {
        foreach ($this->filters as $filter) {
            self::applyFilter($value, null, $filter);
        }
        return $value;
    }

   /**
    * Renders the element using the given renderer
    *
    * @param HTML_QuickForm2_Renderer $renderer
    * @return   HTML_QuickForm2_Renderer
    */
    abstract public function render(HTML_QuickForm2_Renderer $renderer);
}

// set default values for document-wide options
if (null === HTML_Common2::getOption(HTML_QuickForm2_Node::OPTION_ID_FORCE_APPEND_INDEX)) {
    HTML_Common2::setOption(HTML_QuickForm2_Node::OPTION_ID_FORCE_APPEND_INDEX, true);
}
if (null === HTML_Common2::getOption(HTML_QuickForm2_Node::OPTION_LANGUAGE)) {
    HTML_Common2::setOption(HTML_QuickForm2_Node::OPTION_LANGUAGE, 'en');
}
?>
