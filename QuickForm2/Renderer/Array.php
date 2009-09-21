<?php
/**
 * A renderer for HTML_QuickForm2 building an array of form elements 
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2009, Alexey Borzov <avb@php.net>,
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
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @author     Thomas Schulz <ths@4bconsult.de>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Abstract base class for QuickForm2 renderers
 */
require_once 'HTML/QuickForm2/Renderer.php';

/**
 * A renderer for HTML_QuickForm2 building an array of form elements
 *
 * Based on Array renderer from HTML_QuickForm 3.x package
 * 
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @author     Thomas Schulz <ths@4bconsult.de>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Renderer_Array extends HTML_QuickForm2_Renderer
{
   /**
    * Renders a generic element
    *
    * @param    HTML_QuickForm2_Node    Element being rendered
    */
    public function renderElement(HTML_QuickForm2_Node $element)
    {
    }

   /**
    * Renders a hidden element
    *
    * @param    HTML_QuickForm2_Node    Hidden element being rendered
    */
    public function renderHidden(HTML_QuickForm2_Node $element)
    {
    }

   /**
    * Starts rendering a form, called before processing contained elements
    *
    * @param    HTML_QuickForm2_Node    Form being rendered
    */
    public function startForm(HTML_QuickForm2_Node $form)
    {
    }

   /**
    * Finishes rendering a form, called after processing contained elements
    *
    * @param    HTML_QuickForm2_Node    Form being rendered
    */
    public function finishForm(HTML_QuickForm2_Node $form)
    {
    }
    
   /**
    * Starts rendering a generic container, called before processing contained elements
    *
    * @param    HTML_QuickForm2_Node    Container being rendered
    */
    public function startContainer(HTML_QuickForm2_Node $container)
    {
    }

   /**
    * Finishes rendering a generic container, called after processing contained elements
    *
    * @param    HTML_QuickForm2_Node    Container being rendered
    */
    public function finishContainer(HTML_QuickForm2_Node $container)
    {
    }
    
   /**
    * Starts rendering a group, called before processing grouped elements
    *
    * @param    HTML_QuickForm2_Node    Group being rendered
    */
    public function startGroup(HTML_QuickForm2_Node $group)
    {
    }

   /**
    * Finishes rendering a group, called after processing grouped elements
    *
    * @param    HTML_QuickForm2_Node    Group being rendered
    */
    public function finishGroup(HTML_QuickForm2_Node $group)
    {
    }
}
?>
