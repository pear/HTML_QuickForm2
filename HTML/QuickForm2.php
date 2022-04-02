<?php
/**
 * Class representing a HTML form
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
// pear-package-only  * Abstract base class for QuickForm2 containers
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Container.php';

// pear-package-only /**
// pear-package-only  * Data source for HTML_QuickForm2 objects based on superglobal arrays
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/DataSource/SuperGlobal.php';

/**
 * Class representing a HTML form
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2 extends HTML_QuickForm2_Container
{
   /**
    * Data sources providing values for form elements
    * @var HTML_QuickForm2_DataSource[]
    */
    protected $datasources = [];

   /**
    * We do not allow setting "method" and "id" other than through constructor
    * @var string[]
    */
    protected $watchedAttributes = ['id', 'method'];

   /**
    * Class constructor, form's "id" and "method" attributes can only be set here
    *
    * @param string       $id          "id" attribute of <form> tag
    * @param string       $method      HTTP method used to submit the form
    * @param string|array $attributes  Additional HTML attributes
    *                                  (either a string or an array)
    * @param bool         $trackSubmit Whether to track if the form was submitted
    *                                  by adding a special hidden field
    */
    public function __construct(
        $id, $method = 'post', $attributes = null, $trackSubmit = true
    ) {
        $method      = ('GET' == strtoupper($method))? 'get': 'post';
        $trackSubmit = empty($id) ? false : $trackSubmit;
        $this->attributes = array_merge(
            null === $attributes ? [] : self::prepareAttributes($attributes),
            ['method' => $method]
        );
        parent::setId(empty($id) ? null : $id);
        if (!isset($this->attributes['action'])) {
            $this->attributes['action'] = $_SERVER['PHP_SELF'];
        }
        if ($trackSubmit && isset($_REQUEST['_qf__' . $id]) ||
            !$trackSubmit && ('get' == $method && !empty($_GET) ||
                              'post' == $method && (!empty($_POST) || !empty($_FILES)))
        ) {
            $this->addDataSource(new HTML_QuickForm2_DataSource_SuperGlobal($method));
        }
        if ($trackSubmit) {
            $this->appendChild(HTML_QuickForm2_Factory::createElement(
                'hidden', '_qf__' . $id, ['id' => 'qf:' . $id]
            ));
        }
        $this->addFilter([$this, 'skipInternalFields']);
    }

    protected function onAttributeChange($name, $value = null)
    {
        throw new HTML_QuickForm2_InvalidArgumentException(
            'Attribute \'' . $name . '\' is read-only'
        );
    }

    protected function setContainer(HTML_QuickForm2_Container $container = null)
    {
        throw new HTML_QuickForm2_Exception('Form cannot be added to container');
    }

    public function setId($id = null)
    {
        throw new HTML_QuickForm2_InvalidArgumentException(
            "Attribute 'id' is read-only"
        );
    }

    /**
     * {@inheritDoc}
     *
     * Form ID is always set
     *
     * @return string
     */
    public function getId()
    {
        return $this->attributes['id'];
    }


   /**
    * Adds a new data source to the form
    *
    * @param HTML_QuickForm2_DataSource $datasource Data source
    */
    public function addDataSource(HTML_QuickForm2_DataSource $datasource)
    {
        $this->datasources[] = $datasource;
        $this->updateValue();
    }

   /**
    * Replaces the list of form's data sources with a completely new one
    *
    * @param array $datasources A new data source list
    *
    * @throws   HTML_QuickForm2_InvalidArgumentException    if given array
    *               contains something that is not a valid data source
    */
    public function setDataSources(array $datasources)
    {
        foreach ($datasources as $ds) {
            if (!$ds instanceof HTML_QuickForm2_DataSource) {
                throw new HTML_QuickForm2_InvalidArgumentException(
                    'Array should contain only DataSource instances'
                );
            }
        }
        $this->datasources = $datasources;
        $this->updateValue();
    }

   /**
    * Returns the list of data sources attached to the form
    *
    * @return   array
    */
    public function getDataSources()
    {
        return $this->datasources;
    }

    public function getType()
    {
        return 'form';
    }

    public function setValue($value)
    {
        throw new HTML_QuickForm2_Exception('Not implemented');
    }

   /**
    * Tells whether the form was already submitted
    *
    * This is a shortcut for checking whether there is an instance of Submit
    * data source in the list of form data sources
    *
    * @return bool
    */
    public function isSubmitted()
    {
        foreach ($this->datasources as $ds) {
            if ($ds instanceof HTML_QuickForm2_DataSource_Submit) {
                return true;
            }
        }
        return false;
    }

   /**
    * Performs the server-side validation
    *
    * @return   boolean Whether all form's elements are valid
    */
    public function validate()
    {
        return $this->isSubmitted() && parent::validate();
    }

   /**
    * Renders the form using the given renderer
    *
    * @param HTML_QuickForm2_Renderer $renderer
    *
    * @return   HTML_QuickForm2_Renderer
    */
    public function render(HTML_QuickForm2_Renderer $renderer)
    {
        $renderer->startForm($this);
        $renderer->getJavascriptBuilder()->setFormId($this->getId());
        foreach ($this as $element) {
            $element->render($renderer);
        }
        $this->renderClientRules($renderer->getJavascriptBuilder());
        $renderer->finishForm($this);
        return $renderer;
    }

    /**
     * Filter for form's getValue() removing internal fields' values from the array
     *
     * @param array $value
     *
     * @return array
     * @link http://pear.php.net/bugs/bug.php?id=19403
     */
    protected function skipInternalFields($value)
    {
        foreach (array_keys($value) as $key) {
            if ('_qf' === substr($key, 0, 3)) {
                unset($value[$key]);
            }
        }
        return $value;
    }
}
?>
