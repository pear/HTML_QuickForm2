<?php
/**
 * Base class for HTML_QuickForm2 renderers
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006, 2008, Alexey Borzov <avb@php.net>,
 *                           Bertrand Mansion <golgote@mamasam.com>
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
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Abstract base class for QuickForm2 renderers
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
abstract class HTML_QuickForm2_Renderer
{
   /**
    * Stores renderer callbacks by class name
    * @var array
    */
    protected $byClass = array();

   /**
    * Stores renderer callbacks by element name
    * @var array
    */
    protected $byName = array();

   /**
    * Renders a node
    *
    * The method will first check if a PHP callback was given as parameter
    * and then use it to render the node. Otherwise, it will look into
    * the callbacks set by name, comparing them with the node name. Finally,
    * it will try to find a callback using the node class. If no renderer
    * callback is found it will throw an exception.
    *
    * @param    HTML_QuickForm2_Node     Node element to render
    * @param    mixed                    PHP callback used to render the node
    * @return   mixed                    Result of the render operation
    * @throws   HTML_QuickForm2_InvalidArgumentException    if the provided
    *               callback is not callable
    * @throws   HTML_QuickForm2_NotFoundException if the callback is missing
    */
    public function render(HTML_QuickForm2_Node $node, $callback = null)
    {
        if (!is_null($callback)) {
            if (is_callable($callback)) {
                return call_user_func_array($callback, array(&$this, &$node));
            } else {
                throw new HTML_QuickForm2_InvalidArgumentException(
                    "Renderer callback is not valid"
                );
            }
        }
        $name = $node->getName();
        if ($name && isset($this->byName[$name])) {
            return call_user_func_array($this->byName[$name], array(&$this, &$node));
        }
        $class = get_class($node);
        if (isset($this->byClass[$class])) {
            return call_user_func_array($this->byClass[$class], array(&$this, &$node));
        }
        while ($class = get_parent_class($class)) {
            if (isset($this->byClass[$class])) {
                return call_user_func_array($this->byClass[$class], array(&$this, &$node));
            }
        }
        throw new HTML_QuickForm2_NotFoundException(
                "Renderer callback is missing"
            );
    }


   /**
    * Set a renderer callback using node name
    *
    * Nodes can be rendered either using their name or their class. Upon
    * rendering, if a matching callback is found using the node's name,
    * it will be used to render the node.
    *
    * @param    string                   Node name
    * @param    mixed                    PHP callback used to render the node
    * @throws   HTML_QuickForm2_InvalidArgumentException    if the provided
    *               callback is not callable
    */
    public function setByNameRenderer($name, $callback)
    {
        if (!is_callable($callback)) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Renderer callback is not valid"
            );
        }
        $this->byName[$name] = $callback;
    }

   /**
    * Set a renderer callback using nodes class
    *
    * Nodes can be rendered either using their name or their class. Upon
    * rendering, if a matching callback is found using the node's class,
    * it will be used to render the node.
    *
    * @param    string                   Node class
    * @param    mixed                    PHP callback used to render the node
    * @throws   HTML_QuickForm2_InvalidArgumentException    if the provided
    *               callback is not callable
    */
    public function setByClassRenderer($class, $callback)
    {
        if (!is_callable($callback)) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Renderer callback is not valid"
            );
        }
        $this->byClass[$class] = $callback;
    }
}