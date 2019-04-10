<?php
/**
 * Proxy class for HTML_QuickForm2 renderers and their plugins
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
 * @copyright 2006-2019 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

// pear-package-only /**
// pear-package-only  * Abstract base class for QuickForm2 renderers
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Renderer.php';

/**
 * Proxy class for HTML_QuickForm2 renderers and their plugins
 *
 * This class serves two purposes:
 * <ol>
 *   <li>Aggregates renderer and its plugins. From user's point of view
 *       renderer plugins simply add new methods to renderer instances.</li>
 *   <li>Restricts access to renderer properties and methods. Those are defined
 *       as 'public' to allow easy access from plugins, but only methods
 *       with names explicitly returned by Renderer::exportMethods() are
 *       available to the outside world.</li>
 * </ol>
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Renderer_Proxy extends HTML_QuickForm2_Renderer
{
   /**
    * Renderer instance
    * @var HTML_QuickForm2_Renderer
    */
    private $_renderer;

   /**
    * Additional renderer methods to proxy via __call(), as returned by exportMethods()
    * @var array
    */
    private $_rendererMethods = array();

   /**
    * Reference to a list of registered renderer plugins for that renderer type
    * @var array
    */
    private $_pluginClasses;

   /**
    * Plugins for this renderer
    * @var array
    */
    private $_plugins = array();

   /**
    * Plugin methods to call via __call() magic method
    *
    * Array has the form ('lowercase method name' => 'index in _plugins array')
    *
    * @var array
    */
    private $_pluginMethods = array();

   /**
    * Constructor, sets proxied renderer and its plugins
    *
    * @param HTML_QuickForm2_Renderer $renderer       Renderer instance to proxy
    * @param array                    &$pluginClasses Plugins registered for that renderer type
    */
    protected function __construct(HTML_QuickForm2_Renderer $renderer, array &$pluginClasses)
    {
        foreach ($renderer->exportMethods() as $method) {
            $this->_rendererMethods[strtolower($method)] = true;
        }
        $this->_renderer      = $renderer;
        $this->_pluginClasses = &$pluginClasses;
    }

   /**
    * Magic function; call an imported method of a renderer or its plugin
    *
    * @param string $name      method name
    * @param array  $arguments method arguments
    *
    * @return   mixed
    */
    public function __call($name, $arguments)
    {
        $lower = strtolower($name);
        if (isset($this->_rendererMethods[$lower])) {
            // support fluent interfaces
            $ret = call_user_func_array(array($this->_renderer, $name), $arguments);
            return $ret === $this->_renderer? $this: $ret;
        }
        $this->updatePlugins();
        if (isset($this->_pluginMethods[$lower])) {
            return call_user_func_array(
                array($this->_plugins[$this->_pluginMethods[$lower]], $name),
                $arguments
            );
        }
        trigger_error(
            "Fatal error: Call to undefined method " . get_class($this->_renderer)
            . "::" . $name . "()", E_USER_ERROR
        );
    }

   /**
    * Checks whether a method is available in this object
    *
    * A method is considered available if this class has such a public method,
    * if a proxied renderer publishes such a method, if some plugin has such
    * a public method.
    *
    * @param string $name Method name
    *
    * @return bool
    */
    public function methodExists($name)
    {
        $lower  = strtolower($name);
        $exists = parent::methodExists($name) || isset($this->_rendererMethods[$lower]);
        if (!$exists) {
            $this->updatePlugins();
            $exists = isset($this->_pluginMethods[$lower]);
        }
        return $exists;
    }

   /**
    * Updates the list of plugins for the current renderer instance
    *
    * This method checks whether any new plugin classes were registered
    * since its previous invocation and adds instances of these classes to
    * the list. Plugins' methods are imported and can be later called as
    * this object's own.
    *
    * @throws   HTML_QuickForm2_InvalidArgumentException if a plugin has already
    *                   imported name
    */
    protected function updatePlugins()
    {
        for ($i = count($this->_plugins); $i < count($this->_pluginClasses); $i++) {
            list($className, $includeFile) = $this->_pluginClasses[$i];
            HTML_QuickForm2_Loader::loadClass($className, $includeFile, true);

            $methods    = array();
            $plugin     = new $className;
            $reflection = new ReflectionObject($plugin);
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $lower = strtolower($method->getName());
                if ('HTML_QuickForm2_Renderer_Plugin' == $method->getDeclaringClass()->getName()) {
                    continue;
                } elseif (!isset($this->_rendererMethods[$lower])
                          && !isset($this->_pluginMethods[$lower])
                ) {
                    $methods[$lower] = $i;
                } else {
                    throw new HTML_QuickForm2_InvalidArgumentException(
                        'Duplicate method name: name ' . $method->getName() . ' in plugin ' .
                        get_class($plugin) . ' already taken by ' .
                        (isset($this->_rendererMethods[$lower])?
                         get_class($this->_renderer):
                         get_class($this->_plugins[$this->_pluginMethods[$lower]])
                        )
                    );
                }
            }
            $plugin->setRenderer($this->_renderer);
            $this->_plugins[$i]    = $plugin;
            $this->_pluginMethods += $methods;
        }
    }

   /**#@+
    * Proxies for methods defined in {@link HTML_QuickForm2_Renderer}
    */
    public function setOption($nameOrOptions, $value = null)
    {
        $this->_renderer->setOption($nameOrOptions, $value);
        return $this;
    }

    public function getOption($name = null)
    {
        return $this->_renderer->getOption($name);
    }

    public function getJavascriptBuilder()
    {
        return $this->_renderer->getJavascriptBuilder();
    }

    public function setJavascriptBuilder(HTML_QuickForm2_JavascriptBuilder $builder = null)
    {
        $this->_renderer->setJavascriptBuilder($builder);
        return $this;
    }

    public function reset()
    {
        $this->_renderer->reset();
        return $this;
    }

    public function renderElement(HTML_QuickForm2_Node $element)
    {
        $this->_renderer->renderElement($element);
    }

    public function renderHidden(HTML_QuickForm2_Node $element)
    {
        $this->_renderer->renderHidden($element);
    }

    public function startForm(HTML_QuickForm2_Node $form)
    {
        $this->_renderer->startForm($form);
    }

    public function finishForm(HTML_QuickForm2_Node $form)
    {
        $this->_renderer->finishForm($form);
    }

    public function startContainer(HTML_QuickForm2_Node $container)
    {
        $this->_renderer->startContainer($container);
    }

    public function finishContainer(HTML_QuickForm2_Node $container)
    {
        $this->_renderer->finishContainer($container);
    }

    public function startGroup(HTML_QuickForm2_Node $group)
    {
        $this->_renderer->startGroup($group);
    }

    public function finishGroup(HTML_QuickForm2_Node $group)
    {
        $this->_renderer->finishGroup($group);
    }
   /**#@-*/

    public function __toString()
    {
        if (method_exists($this->_renderer, '__toString')) {
            return $this->_renderer->__toString();
        }
        trigger_error(
            "Fatal error: Object of class " . get_class($this->_renderer)
            . " could not be converted to string", E_USER_ERROR
        );
    }
}
?>