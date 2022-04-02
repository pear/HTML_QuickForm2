<?php
/**
 * Class for <input type="file" /> elements
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
// pear-package-only  * Base class for <input> elements
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Element/Input.php';

/**
 * Class for <input type="file" /> elements
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Element_InputFile extends HTML_QuickForm2_Element_Input
{
   /**
    * Language to display error messages in
    * @var  string|null
    */
    protected $language = null;

   /**
    * Information on uploaded file, from submit data source
    * @var array|null
    */
    protected $value = null;

    protected $attributes = ['type' => 'file'];

   /**
    * Message provider for upload error messages
    * @var  callable|HTML_QuickForm2_MessageProvider
    */
    protected $messageProvider;

   /**
    * Class constructor
    *
    * Possible keys in $data array are:
    *  - 'messageProvider': an instance of a class implementing
    *    HTML_QuickForm2_MessageProvider interface, this will be used to get
    *    localized error messages. Default will be used if not given.
    *  - 'language': language to display error messages in, will be passed to
    *    message provider.
    *
    * @param string       $name       Element name
    * @param string|array $attributes Attributes (either a string or an array)
    * @param array        $data       Data used to set up error messages for PHP's
    *                                 file upload errors.
    */
    public function __construct($name = null, $attributes = null, array $data = [])
    {
        if (isset($data['messageProvider'])) {
            if (!is_callable($data['messageProvider'])
                && !$data['messageProvider'] instanceof HTML_QuickForm2_MessageProvider
            ) {
                throw new HTML_QuickForm2_InvalidArgumentException(
                    "messageProvider: expecting a callback or an implementation"
                    . " of HTML_QuickForm2_MessageProvider"
                );
            }
            $this->messageProvider = $data['messageProvider'];

        } else {
            // pear-package-only HTML_QuickForm2_Loader::loadClass('HTML_QuickForm2_MessageProvider_Default');
            $this->messageProvider = HTML_QuickForm2_MessageProvider_Default::getInstance();
        }
        if (isset($data['language'])) {
            $this->language = $data['language'];
        }
        unset($data['messageProvider'], $data['language']);
        parent::__construct($name, $attributes, $data);
    }


   /**
    * File upload elements cannot be frozen
    *
    * To properly "freeze" a file upload element one has to store the uploaded
    * file somewhere and store the file info in session. This is way outside
    * the scope of this class.
    *
    * @param bool $freeze Whether element should be frozen or editable. This
    *                     parameter is ignored in case of file uploads
    *
    * @return   bool    Always returns false
    */
    public function toggleFrozen($freeze = null)
    {
        return false;
    }

   /**
    * Returns the information on uploaded file
    *
    * @return   array|null
    */
    public function getRawValue()
    {
        return $this->value;
    }

   /**
    * Alias of getRawValue(), InputFile elements do not allow filters
    *
    * @return   array|null
    */
    public function getValue()
    {
        return $this->getRawValue();
    }

   /**
    * File upload's value cannot be set here
    *
    * @param mixed $value Value for file element, this parameter is ignored
    *
    * @return $this
    */
    public function setValue($value)
    {
        return $this;
    }

    protected function updateValue()
    {
        // request #16807: file uploads should not be added to forms with
        // method="get", enctype should be set to multipart/form-data
        // we cannot do this in setContainer() as the element may be added to
        // e.g. a group first and then the group may be added to a form
        $container = $this->getContainer();
        while (!empty($container)) {
            if ($container instanceof HTML_QuickForm2) {
                if ('get' == $container->getAttribute('method')) {
                    throw new HTML_QuickForm2_InvalidArgumentException(
                        'File upload elements can only be added to forms with post submit method'
                    );
                }
                if ('multipart/form-data' != $container->getAttribute('enctype')) {
                    $container->setAttribute('enctype', 'multipart/form-data');
                }
                break;
            }
            $container = $container->getContainer();
        }

        foreach ($this->getDataSources() as $ds) {
            if ($ds instanceof HTML_QuickForm2_DataSource_Submit) {
                $value = $ds->getUpload((string)$this->getName());
                if (null !== $value) {
                    $this->value = $value;
                    return;
                }
            }
        }
        $this->value = null;
    }

   /**
    * Performs the server-side validation
    *
    * Before the Rules added to the element kick in, the element checks the
    * error code added to the $_FILES array by PHP. If the code isn't
    * UPLOAD_ERR_OK or UPLOAD_ERR_NO_FILE then a built-in error message will be
    * displayed and no further validation will take place.
    *
    * @return   boolean     Whether the element is valid
    */
    protected function validate()
    {
        if ('' !== $this->error) {
            return false;
        }
        if (isset($this->value['error'])
            && !in_array($this->value['error'], [UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE])
        ) {
            /** @var string $errorMessage */
            $errorMessage = $this->messageProvider instanceof HTML_QuickForm2_MessageProvider
                            ? $this->messageProvider->get(['file', $this->value['error']], $this->language)
                            : call_user_func($this->messageProvider, ['file', $this->value['error']], $this->language);
            if (UPLOAD_ERR_INI_SIZE == $this->value['error']) {
                $iniSize = ini_get('upload_max_filesize');
                $size    = intval($iniSize);
                switch (strtoupper(substr($iniSize, -1))) {
                case 'G': $size *= 1024;
                case 'M': $size *= 1024;
                case 'K': $size *= 1024;
                }

            } elseif (UPLOAD_ERR_FORM_SIZE == $this->value['error']) {
                foreach ($this->getDataSources() as $ds) {
                    if ($ds instanceof HTML_QuickForm2_DataSource_Submit) {
                        $size = intval($ds->getValue('MAX_FILE_SIZE'));
                        break;
                    }
                }
            }
            $this->error = isset($size)? sprintf($errorMessage, $size): $errorMessage;
            return false;
        }
        return parent::validate();
    }

    public function addFilter($callback, array $options = [])
    {
        throw new HTML_QuickForm2_Exception(
            'InputFile elements do not support filters'
        );
    }

    public function addRecursiveFilter($callback, array $options = [])
    {
        throw new HTML_QuickForm2_Exception(
            'InputFile elements do not support filters'
        );
    }
}
?>
