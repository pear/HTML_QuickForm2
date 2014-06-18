<?php
/**
 * Class for <input type="hidden" /> elements
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
 * Base class for <input> elements
 */
require_once 'HTML/QuickForm2/Element/Input.php';

/**
 * Class for <input type="hidden" /> elements
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Element_InputHidden extends HTML_QuickForm2_Element_Input
{
    protected $attributes = array('type' => 'hidden');

   /**
    * Hidden elements can not be frozen
    *
    * @param bool $freeze Whether element should be frozen or editable. This
    *                     parameter is ignored in case of hidden elements
    *
    * @return   bool    Always returns false
    */
    public function toggleFrozen($freeze = null)
    {
        return false;
    }

    /**
     * Disallows setting an error message on hidden elements
     *
     * @param string|null $error
     *
     * @return HTML_QuickForm2_Element_InputHidden
     * @throws HTML_QuickForm2_InvalidArgumentException if $error is not empty
     */
    public function setError($error = null)
    {
        if (strlen($error)) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Hidden elements cannot have validation errors"
            );
        }
        return parent::setError($error);
    }

    public function render(HTML_QuickForm2_Renderer $renderer)
    {
        $renderer->renderHidden($this);
        $this->renderClientRules($renderer->getJavascriptBuilder());
        return $renderer;
    }
}
?>
