<?php
/**
 * Object wrapping around session variable used to store controller data
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

/**
 * Object wrapping around session variable used to store controller data
 *
 * This is a bit less fragile than allowing to directly manipulate the array
 * as was done in old HTML_QuickForm_Controller package
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Controller_SessionContainer
{
   /**
    * A reference to a key in $_SESSION superglobal array
    * @var array
    */
    protected $data;

   /**
    * Class constructor
    *
    * Initializes a variable in $_SESSION array, its name is based upon the
    * name of the Controller passed here
    *
    * @param HTML_QuickForm2_Controller $controller
    */
    public function __construct(HTML_QuickForm2_Controller $controller)
    {
        $name = sprintf(
            HTML_QuickForm2_Controller::KEY_CONTAINER, $controller->getId()
        );
        if (empty($_SESSION[$name])) {
            $_SESSION[$name] = array(
                'datasources' => array(),
                'values'      => array(),
                'valid'       => array()
            );
        }
        $this->data =& $_SESSION[$name];
    }

   /**
    * Stores the page submit values
    *
    * @param string $pageId Page ID
    * @param array  $values Page submit values
    */
    public function storeValues($pageId, array $values)
    {
        $this->data['values'][$pageId] = $values;
    }

   /**
    * Returns the page values kept in session
    *
    * @param string $pageId Page ID
    *
    * @return array
    */
    public function getValues($pageId)
    {
        return array_key_exists($pageId, $this->data['values'])
               ? $this->data['values'][$pageId]: array();
    }

   /**
    * Stores the page validation status
    *
    * @param string $pageId Page ID
    * @param bool   $status Whether the page is valid
    */
    public function storeValidationStatus($pageId, $status)
    {
        $this->data['valid'][$pageId] = (bool)$status;
    }

   /**
    * Returns the page validation status kept in session
    *
    * @param string $pageId Page ID
    *
    * @return   bool
    */
    public function getValidationStatus($pageId)
    {
        return array_key_exists($pageId, $this->data['valid'])
               ? $this->data['valid'][$pageId]: null;

    }

   /**
    * Stores the controller data sources
    *
    * @param array $datasources A new data source list
    *
    * @throws   HTML_QuickForm2_InvalidArgumentException    if given array
    *               contains something that is not a valid data source
    */
    public function storeDatasources(array $datasources)
    {
        foreach ($datasources as $ds) {
            if (!$ds instanceof HTML_QuickForm2_DataSource) {
                throw new HTML_QuickForm2_InvalidArgumentException(
                    'Array should contain only DataSource instances'
                );
            }
        }
        $this->data['datasources'] = $datasources;
    }

   /**
    * Returns the controller data sources
    *
    * @return array
    */
    public function getDatasources()
    {
        return $this->data['datasources'];
    }

   /**
    * Stores some user-supplied parameter alongside controller data
    *
    * It is sometimes useful to pass some additional user data between pages
    * of the form, thus this method. Data added by this method will be removed
    * alongside all the other data by
    * {@link HTML_QuickForm2_Controller::destroySessionContainer()}
    *
    * @param string $name  Parameter name
    * @param string $value Parameter value
    */
    public function storeOpaque($name, $value)
    {
        if (!array_key_exists('opaque', $this->data)) {
            $this->data['opaque'] = array();
        }
        $this->data['opaque'][$name] = $value;
    }

   /**
    * Returns a user-supplied parameter
    *
    * @param string $name Parameter name
    *
    * @return   mixed
    */
    public function getOpaque($name)
    {
        return (array_key_exists('opaque', $this->data)
                && array_key_exists($name, $this->data['opaque']))
               ? $this->data['opaque'][$name]: null;
    }
}