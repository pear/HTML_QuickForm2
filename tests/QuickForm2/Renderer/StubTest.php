<?php
/**
 * Unit tests for HTML_QuickForm2 package
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