<?php
/**
 * A renderer for HTML_QuickForm2 building an array of form elements
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
 * @author    Thomas Schulz <ths@4bconsult.de>
 * @copyright 2006-2021 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

// pear-package-only /**
// pear-package-only  * Abstract base class for QuickForm2 renderers
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Renderer.php';

/**
 * A renderer for HTML_QuickForm2 building an array of form elements
 *
 * Based on Array renderer from HTML_QuickForm 3.x package
 *
 * The form array structure is the following:
 * <pre>
 * array(
 *   'id'               => form's "id" attribute (string),
 *   'frozen'           => whether the form is frozen (bool),
 *   'attributes'       => attributes for &lt;form&gt; tag (string),
 *   // if form contains required elements:
 *   'required_note'    => note about the required elements (string),
 *   // if 'group_hiddens' option is true:
 *   'hidden'           => array with html of hidden elements (array),
 *   // if form has some javascript for setup or validation:
 *   'javascript'       => form javascript (string)
 *   // if 'group_errors' option is true:
 *   'errors' => array(
 *     '1st element id' => 'Error for the 1st element',
 *     ...
 *     'nth element id' => 'Error for the nth element'
 *   ),
 *   'elements' => array(
 *     element_1,
 *     ...
 *     element_N
 *   )
 * );
 * </pre>
 * Where element_i is an array of the form
 * <pre>
 * array(
 *   'id'        => element id (string),
 *   'type'      => type of the element (string),
 *   'frozen'    => whether element is frozen (bool),
 *   // if element has a label:
 *   'label'     => 'label for the element',
 *   // note that if 'static_labels' option is true and element's label is an
 *   // array then there will be several 'label_*' keys corresponding to
 *   // labels' array keys
 *   'required'  => whether element is required (bool),
 *   // if a validation error is present and 'group_errors' option is false:
 *   'error'     => error associated with the element (string),
 *   // if some style was associated with an element:
 *   'style'     => 'some information about element style (e.g. for Smarty)',
 *
 *   // if element is not a Container
 *   'value'     => element value (mixed),
 *   'html'      => HTML for the element (string),
 *
 *   // if element is a Container
 *   'attributes' => container attributes (string)
 *   // if element is a Group
 *   'class'      => element's 'class' attribute
 *   // only for groups, if separator is set:
 *   'separator'  => separator for group elements (array),
 *   'elements'   => array(
 *     element_1,
 *     ...
 *     element_N
 *   )
 * );
 * </pre>
 *
 * While almost everything in this class is defined as public, its properties
 * and those methods that are not published (i.e. not in array returned by
 * exportMethods()) will be available to renderer plugins only.
 *
 * The following methods are published:
 *   - {@link toArray()}
 *   - {@link setStyleForId()}
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @author   Thomas Schulz <ths@4bconsult.de>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Renderer_Array extends HTML_QuickForm2_Renderer
{
   /**
    * An array being generated
    * @var array
    */
    public $array = [];

   /**
    * Array with references to 'elements' fields of currently processed containers
    * @var array
    */
    public $containers = [];

   /**
    * Whether the form contains required elements
    * @var  bool
    */
    public $hasRequired = false;

   /**
    * Additional style information for elements
    * @var array
    */
    public $styles = [];

   /**
    * Constructor, adds a new 'static_labels' option
    */
    protected function __construct()
    {
        $this->options['static_labels'] = false;
    }

    protected function exportMethods()
    {
        return [
            'toArray',
            'setStyleForId'
        ];
    }

   /**
    * Resets the accumulated data
    *
    * This method is called automatically by startForm() method, but should
    * be called manually before calling other rendering methods separately.
    *
    * @return $this
    */
    public function reset()
    {
        $this->array       = [];
        $this->containers  = [];
        $this->hasRequired = false;

        return $this;
    }

   /**
    * Returns the resultant array
    *
    * @return array
    */
    public function toArray()
    {
        return $this->array;
    }

   /**
    * Creates an array with fields that are common to all elements
    *
    * @param HTML_QuickForm2_Node $element Element being rendered
    *
    * @return   array
    */
    public function buildCommonFields(HTML_QuickForm2_Node $element)
    {
        $ary = [
            'id'                 => $element->getId(),
            'frozen'             => $element->toggleFrozen(),
            'element-attributes' => $element->getAttributes(),
        ];
        if ($labels = $element->getLabel()) {
            if (!is_array($labels) || !$this->options['static_labels']) {
                $ary['label'] = $labels;
            } else {
                foreach ($labels as $key => $label) {
                    $key = is_int($key)? $key + 1: $key;
                    if (1 === $key) {
                        $ary['label'] = $label;
                    } else {
                        $ary['label_' . $key] = $label;
                    }
                }
            }
        }
        if (($error = $element->getError()) && $this->options['group_errors']) {
            $this->array['errors'][$ary['id']] = $error;
        } elseif ($error) {
            $ary['error'] = $error;
        }
        if (isset($this->styles[$ary['id']])) {
            $ary['style'] = $this->styles[$ary['id']];
        }
        return $ary;
    }

    /**
     * Creates an array with fields that are common to all Containers
     *
     * @param HTML_QuickForm2_Node $container Container being rendered
     *
     * @return array
     */
    public function buildCommonContainerFields(HTML_QuickForm2_Node $container)
    {
        return $this->buildCommonFields($container) + [
            'elements'   => [],
            'attributes' => $container->getAttributes(true)
            ];
    }

   /**
    * Stores an array representing "scalar" element in the form array
    *
    * @param array $element
    */
    public function pushScalar(array $element)
    {
        if (!empty($element['required'])) {
            $this->hasRequired = true;
        }
        if (empty($this->containers)) {
            $this->array += $element;
        } else {
            $this->containers[count($this->containers) - 1][] = $element;
        }
    }

   /**
    * Stores an array representing a Container in the form array
    *
    * @param array $container
    */
    public function pushContainer(array $container)
    {
        if (!empty($container['required'])) {
            $this->hasRequired = true;
        }
        if (empty($this->containers)) {
            $this->array      += $container;
            $this->containers  = [&$this->array['elements']];
        } else {
            $cntIndex = count($this->containers) - 1;
            $myIndex  = count($this->containers[$cntIndex]);
            $this->containers[$cntIndex][$myIndex] = $container;
            $this->containers[$cntIndex + 1] =& $this->containers[$cntIndex][$myIndex]['elements'];
        }
    }

   /**
    * Sets a style for element rendering
    *
    * "Style" is some information that is opaque to Array Renderer but may be
    * of use to e.g. template engine that receives the resultant array.
    *
    * @param string|array $idOrStyles Element id or array ('element id' => 'style')
    * @param mixed        $style      Element style if $idOrStyles is not an array
    *
    * @return $this
    */
    public function setStyleForId($idOrStyles, $style = null)
    {
        if (is_array($idOrStyles)) {
            $this->styles = array_merge($this->styles, $idOrStyles);
        } else {
            $this->styles[$idOrStyles] = $style;
        }
        return $this;
    }

   /**#@+
    * Implementations of abstract methods from {@link HTML_QuickForm2_Renderer}
    */
    public function renderElement(HTML_QuickForm2_Node $element)
    {
        $ary = $this->buildCommonFields($element) + [
            'html'     => $element->__toString(),
            'value'    => $element->getValue(),
            'type'     => $element->getType(),
            'required' => $element->isRequired(),
            ];
        $this->pushScalar($ary);
    }

    public function renderHidden(HTML_QuickForm2_Node $element)
    {
        if ($this->options['group_hiddens']) {
            $this->array['hidden'][] = $element->__toString();
        } else {
            $this->renderElement($element);
        }
    }

    public function startForm(HTML_QuickForm2_Node $form)
    {
        $this->reset();

        $this->array = $this->buildCommonContainerFields($form);
        if ($this->options['group_errors']) {
            $this->array['errors'] = [];
        }
        if ($this->options['group_hiddens']) {
            $this->array['hidden'] = [];
        }
        $this->containers  = [&$this->array['elements']];
    }

    public function finishForm(HTML_QuickForm2_Node $form)
    {
        $this->finishContainer($form);
        if ($this->hasRequired) {
            $this->array['required_note'] = $this->options['required_note'];
        }
        $this->array['javascript'] = $this->getJavascriptBuilder()->getFormJavascript($form->getId());
    }

    public function startContainer(HTML_QuickForm2_Node $container)
    {
        $ary = $this->buildCommonContainerFields($container) + [
            'required' => $container->isRequired(),
            'type'     => $container->getType()
            ];
        $this->pushContainer($ary);
    }

    public function finishContainer(HTML_QuickForm2_Node $container)
    {
        array_pop($this->containers);
    }

    public function startGroup(HTML_QuickForm2_Node $group)
    {
        $ary = $this->buildCommonContainerFields($group) + [
            'required' => $group->isRequired(),
            'type'     => $group->getType(),
            'class'    => $group->getAttribute('class')
            ];
        if ($separator = $group->getSeparator()) {
            $ary['separator'] = [];
            for ($i = 0, $count = count($group); $i < $count - 1; $i++) {
                if (!is_array($separator)) {
                    $ary['separator'][] = (string)$separator;
                } else {
                    $ary['separator'][] = $separator[$i % count($separator)];
                }
            }
        }
        $this->pushContainer($ary);
    }

    public function finishGroup(HTML_QuickForm2_Node $group)
    {
        $this->finishContainer($group);
    }
    /**#@-*/
}
?>
