<?php
/**
 * Unit tests for HTML_QuickForm2 package
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
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/** Sets up includes */
require_once dirname(dirname(dirname(__FILE__))) . '/TestHelper.php';

/**
 * Unit test for HTML_QuickForm2_Renderer_Stub class
 */
class HTML_QuickForm2_Renderer_StubTest extends PHPUnit_Framework_TestCase
{
    public function testHasRequired()
    {
        $form     = new HTML_QuickForm2('testHasRequired');
        $text     = $form->addText('anElement');
        $renderer = HTML_QuickForm2_Renderer::factory('stub');

        $form->render($renderer);
        $this->assertFalse($renderer->hasRequired());

        $text->addRule('required', 'element is required');
        $form->render($renderer);
        $this->assertTrue($renderer->hasRequired());
    }

    public function testGroupErrors()
    {
        $form     = new HTML_QuickForm2('testGroupErrors');
        $text     = $form->addText('anElement', array('id' => 'anElement'))
                        ->setError('an error');
        $renderer = HTML_QuickForm2_Renderer::factory('stub');

        $renderer->setOption('group_errors', false);
        $form->render($renderer);
        $this->assertEquals(array(), $renderer->getErrors());

        $renderer->setOption('group_errors', true);
        $form->render($renderer);
        $this->assertEquals(array('anElement' => 'an error'), $renderer->getErrors());
    }

    public function testGroupHiddens()
    {
        $form     = new HTML_QuickForm2('testGroupHiddens', 'post', null, false);
        $hidden   = $form->addHidden('aHiddenElement');
        $renderer = HTML_QuickForm2_Renderer::factory('stub');

        $renderer->setOption('group_hiddens', false);
        $form->render($renderer);
        $this->assertEquals(array(), $renderer->getHidden());

        $renderer->setOption('group_hiddens', true);
        $form->render($renderer);
        $this->assertEquals(array($hidden->__toString()), $renderer->getHidden());
    }
}
?>