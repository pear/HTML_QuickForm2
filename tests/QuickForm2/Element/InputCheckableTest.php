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
 * @copyright 2006-2023 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

/** Sets up includes */
require_once dirname(dirname(__DIR__)) . '/TestHelper.php';

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Unit test for HTML_QuickForm2_Element_InputCheckable class
 */
class HTML_QuickForm2_Element_InputCheckableTest extends TestCase
{
    public function testConstructorSetsContent()
    {
        $checkable = new HTML_QuickForm2_Element_InputCheckable('foo', null, ['content' => 'I am foo']);
        $this->assertEquals('I am foo', $checkable->getContent());
    }

    public function testContentRendering()
    {
        $checkable = new HTML_QuickForm2_Element_InputCheckable(
            'foo', ['id' => 'checkableFoo'], ['content' => 'I am foo']
        );
        $this->assertMatchesRegularExpression(
            '!<label\\s+for="checkableFoo">I am foo</label>!',
            $checkable->__toString()
        );

        $checkable->toggleFrozen(true);
        $this->assertDoesNotMatchRegularExpression('!<label!', $checkable->__toString());

        $checkable->toggleFrozen(false);
        $this->assertSame($checkable, $checkable->setContent(''));
        $this->assertDoesNotMatchRegularExpression('!<label!', $checkable->__toString());
    }

    public function testEmptyContentRendering()
    {
        $checkable = new HTML_QuickForm2_Element_InputCheckable(
            'foo1', ['id' => 'checkableFoo1']
        );
        $this->assertDoesNotMatchRegularExpression('!<label!', $checkable->__toString());
    }

    public function testSetAndGetValue()
    {
        $checkable = new HTML_QuickForm2_Element_InputCheckable();
        $checkable->setAttribute('value', 'my value');

        $this->assertNull($checkable->getValue());

        $this->assertSame($checkable, $checkable->setValue('my value'));
        $this->assertEquals('checked', $checkable->getAttribute('checked'));
        $this->assertEquals('my value', $checkable->getValue());

        $this->assertSame($checkable, $checkable->setValue('not my value!'));
        $this->assertNull($checkable->getAttribute('checked'));
        $this->assertNull($checkable->getValue());

        $checkable->setAttribute('checked');
        $this->assertEquals('my value', $checkable->getValue());
    }

    public function testGetValueDisabled()
    {
        $checkable = new HTML_QuickForm2_Element_InputCheckable();
        $checkable->setAttribute('value', 'my value');

        $checkable->setValue('my value');
        $checkable->setAttribute('disabled');
        $this->assertEquals('checked', $checkable->getAttribute('checked'));
        $this->assertNull($checkable->getValue());
    }

    public function testFrozenHtmlGeneration()
    {
        $checkable = new HTML_QuickForm2_Element_InputCheckable(
            'checkableFreeze', ['value' => 'my value'], ['content' => 'freeze me']
        );
        $checkable->setAttribute('checked');

        $checkable->toggleFrozen(true);
        $this->assertMatchesRegularExpression('!<input[^>]*type="hidden"[^>]*/>!', $checkable->__toString());

        $checkable->removeAttribute('checked');
        $this->assertDoesNotMatchRegularExpression('!<input!', $checkable->__toString());
    }

    public function testBug15708()
    {
        $form = new HTML_QuickForm2('bug15708');
        $form->addDataSource(new HTML_QuickForm2_DataSource_Array([
            'aRadio' => 1
        ]));
        $aRadio = $form->appendChild(
                            new HTML_QuickForm2_Element_InputCheckable('aRadio')
                      )->setAttribute('value', 1);
        $this->assertStringContainsString('checked', $aRadio->__toString());
    }

}
?>
