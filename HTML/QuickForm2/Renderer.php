<?php
/**
 * Base class for HTML_QuickForm2 renderers
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
 * @copyright 2006-2023 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

// pear-package-only /**
// pear-package-only  * Class with static methods for loading classes and files
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Loader.php';

/**
 * Abstract base class for QuickForm2 renderers
 *
 * This class serves two main purposes:
 * <ul>
 *   <li>Defines the API all renderers should implement (render*() methods);</li>
 *   <li>Provides static methods for registering renderers and their plugins
 *       and {@link factory()} method for creating renderer instances.</li>
 * </ul>
 *
 * Note that renderers should always be instantiated through factory(), in the
 * other case it will not be possible to add plugins.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
abstract class HTML_QuickForm2_Renderer
{
   /**
    * List of registered renderer types
    * @var array<string, array{class-string<HTML_QuickForm2_Renderer>, string|null}>
    */
    private static $_types = [
        'callback' => [HTML_QuickForm2_Renderer_Callback::class, null],
        'default'  => [HTML_QuickForm2_Renderer_Default::class, null],
        'array'    => [HTML_QuickForm2_Renderer_Array::class, null],
        'stub'     => [HTML_QuickForm2_Renderer_Stub::class, null]
    ];

   /**
    * List of registered renderer plugins
    * @var array<string, array<int, array{class-string, string|null}>>
    */
    private static $_pluginClasses = [
        'callback' => [],
        'default'  => [],
        'array'    => [],
        'stub'     => []
    ];

   /**
    * Renderer options
    * @var  array
    * @see  setOption()
    */
    protected $options = [
        'group_hiddens' => true,
        'required_note' => '<em>*</em> denotes required fields.',
        'errors_prefix' => 'Invalid information entered:',
        'errors_suffix' => 'Please correct these fields.',
        'group_errors'  => false
    ];

   /**
    * Javascript builder object
    * @var  HTML_QuickForm2_JavascriptBuilder|null
    */
    protected $jsBuilder;

   /**
    * Creates a new renderer instance of the given type
    *
    * A renderer is always wrapped by a Proxy, which handles calling its
    * "published" methods and methods of its plugins. Registered plugins are
    * added automagically to the existing renderer instances so that
    * <code>
    * $foo = HTML_QuickForm2_Renderer::factory('foo');
    * // Plugin implementing bar() method
    * HTML_QuickForm2_Renderer::registerPlugin('foo', 'Plugin_Foo_Bar');
    * $foo->bar();
    * </code>
    * will work.
    *
    * @param string $type Type name (treated case-insensitively)
    *
    * @return   HTML_QuickForm2_Renderer_Proxy  A renderer instance of the given
    *                   type wrapped by a Proxy
    * @throws   HTML_QuickForm2_InvalidArgumentException If type name is unknown
    * @throws   HTML_QuickForm2_NotFoundException If class for the renderer can
    *           not be found and/or loaded from file
    */
    final public static function factory($type)
    {
        $type = strtolower($type);
        if (!isset(self::$_types[$type])) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Renderer type '$type' is not known"
            );
        }

        /** @var class-string<HTML_QuickForm2_Renderer> $className */
        list ($className, $includeFile) = self::$_types[$type];
        HTML_QuickForm2_Loader::loadClass($className, $includeFile, true);
        // pear-package-only HTML_QuickForm2_Loader::loadClass('HTML_QuickForm2_Renderer_Proxy');
        return new HTML_QuickForm2_Renderer_Proxy(new $className, self::$_pluginClasses[$type]);
    }

   /**
    * Registers a new renderer type
    *
    * @param string                                 $type        Type name (treated case-insensitively)
    * @param class-string<HTML_QuickForm2_Renderer> $className   Class name
    * @param string|null                            $includeFile File containing the class, leave empty
    *                                                            if class already loaded
    */
    final public static function register($type, $className, $includeFile = null)
    {
        $type = strtolower($type);
        self::$_types[$type] = [$className, $includeFile];
        if (empty(self::$_pluginClasses[$type])) {
            self::$_pluginClasses[$type] = [];
        }
    }

   /**
    * Registers a plugin for a renderer type
    *
    * @param string       $type        Renderer type name (treated case-insensitively)
    * @param class-string $className   Plugin class name
    * @param string|null  $includeFile File containing the plugin class, leave empty if class already loaded
    *
    * @throws   HTML_QuickForm2_InvalidArgumentException if plugin is already registered
    */
    final public static function registerPlugin($type, $className, $includeFile = null)
    {
        $type = strtolower($type);
        // We don't check self::$_types, since a plugin may be registered
        // before renderer itself if it goes with some custom element
        if (empty(self::$_pluginClasses[$type])) {
            self::$_pluginClasses[$type] = [[$className, $includeFile]];
        } else {
            foreach (self::$_pluginClasses[$type] as $plugin) {
                if (0 == strcasecmp($plugin[0], $className)) {
                    throw new HTML_QuickForm2_InvalidArgumentException(
                        "Plugin '$className' for renderer type '$type' is already registered"
                    );
                }
            }
            self::$_pluginClasses[$type][] = [$className, $includeFile];
        }
    }

   /**
    * Constructor
    *
    * Renderer instances should not be created directly, use {@link factory()}
    */
    protected function __construct()
    {
    }

   /**
    * Returns an array of "published" method names that should be callable through proxy
    *
    * Methods defined in HTML_QuickForm2_Renderer are proxied automatically,
    * only additional methods should be returned.
    *
    * @return   array
    */
    protected function exportMethods()
    {
        return [];
    }

   /**
    * Checks whether a method is available in this object
    *
    * @param string $name Method name
    *
    * @return bool
    */
    public function methodExists($name)
    {
        try {
            $method = new ReflectionMethod($this, $name);
            return $method->isPublic();
        } catch (ReflectionException $e) {
            return false;
        }
    }

   /**
    * Sets the option(s) affecting renderer behaviour
    *
    * The following options are available:
    * <ul>
    *   <li>'group_hiddens' - whether to group hidden elements together or
    *                         render them where they were added (boolean)</li>
    *   <li>'group_errors'  - whether to group error messages or render them
    *                         alongside elements they apply to (boolean)</li>
    *   <li>'errors_prefix' - leading message for grouped errors (string)</li>
    *   <li>'errors_suffix' - trailing message for grouped errors (string)</li>
    *   <li>'required_note' - note displayed if the form contains required
    *                         elements (string)</li>
    * </ul>
    *
    * @param string|array $nameOrOptions option name or array ('option name' => 'option value')
    * @param mixed        $value         parameter value if $nameOrConfig is not an array
    *
    * @return   $this
    * @throws   HTML_QuickForm2_NotFoundException in case of unknown option
    */
    public function setOption($nameOrOptions, $value = null)
    {
        if (is_array($nameOrOptions)) {
            foreach ($nameOrOptions as $name => $value) {
                $this->setOption($name, $value);
            }

        } else {
            if (!array_key_exists($nameOrOptions, $this->options)) {
                throw new HTML_QuickForm2_NotFoundException(
                    "Unknown option '{$nameOrOptions}'"
                );
            }
            $this->options[$nameOrOptions] = $value;
        }

        return $this;
    }

   /**
    * Returns the value(s) of the renderer option(s)
    *
    * @param string $name parameter name
    *
    * @return   mixed   value of $name parameter, array of all configuration
    *                   parameters if $name is not given
    * @throws   HTML_QuickForm2_NotFoundException in case of unknown option
    */
    public function getOption($name = null)
    {
        if (null === $name) {
            return $this->options;
        } elseif (!array_key_exists($name, $this->options)) {
            throw new HTML_QuickForm2_NotFoundException(
                "Unknown option '{$name}'"
            );
        }
        return $this->options[$name];
    }

   /**
    * Returns the javascript builder object
    *
    * @return   HTML_QuickForm2_JavascriptBuilder
    */
    public function getJavascriptBuilder()
    {
        if (empty($this->jsBuilder)) {
            // pear-package-only HTML_QuickForm2_Loader::loadClass('HTML_QuickForm2_JavascriptBuilder');
            $this->jsBuilder = new HTML_QuickForm2_JavascriptBuilder();
        }
        return $this->jsBuilder;
    }

   /**
    * Sets the javascript builder object
    *
    * You may want to reuse the same builder object if outputting several
    * forms on one page.
    *
    * @param    HTML_QuickForm2_JavascriptBuilder $builder
    *
    * @return $this
    */
    public function setJavascriptBuilder(HTML_QuickForm2_JavascriptBuilder $builder = null)
    {
        $this->jsBuilder = $builder;
        return $this;
    }

   /**
    * Resets the accumulated data
    *
    * This method is called automatically by startForm() method, but should
    * be called manually before calling other rendering methods separately.
    *
    * @return $this
    */
    abstract public function reset();

   /**
    * Renders a generic element
    *
    * @param HTML_QuickForm2_Node $element Element being rendered
    */
    abstract public function renderElement(HTML_QuickForm2_Node $element);

   /**
    * Renders a hidden element
    *
    * @param HTML_QuickForm2_Node $element Hidden element being rendered
    */
    abstract public function renderHidden(HTML_QuickForm2_Node $element);

   /**
    * Starts rendering a form, called before processing contained elements
    *
    * @param HTML_QuickForm2_Node $form Form being rendered
    */
    abstract public function startForm(HTML_QuickForm2_Node $form);

   /**
    * Finishes rendering a form, called after processing contained elements
    *
    * @param HTML_QuickForm2_Node $form Form being rendered
    */
    abstract public function finishForm(HTML_QuickForm2_Node $form);

   /**
    * Starts rendering a generic container, called before processing contained elements
    *
    * @param HTML_QuickForm2_Node $container Container being rendered
    */
    abstract public function startContainer(HTML_QuickForm2_Node $container);

   /**
    * Finishes rendering a generic container, called after processing contained elements
    *
    * @param HTML_QuickForm2_Node $container Container being rendered
    */
    abstract public function finishContainer(HTML_QuickForm2_Node $container);

   /**
    * Starts rendering a group, called before processing grouped elements
    *
    * @param HTML_QuickForm2_Node $group Group being rendered
    */
    abstract public function startGroup(HTML_QuickForm2_Node $group);

   /**
    * Finishes rendering a group, called after processing grouped elements
    *
    * @param HTML_QuickForm2_Node $group Group being rendered
    */
    abstract public function finishGroup(HTML_QuickForm2_Node $group);
}
?>
