<?php
/**
 * Base class for fieldsets
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

// pear-package-only /**
// pear-package-only  * Base class for fieldsets
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Container.php';

/**
 * Concrete implementation of a container for fieldsets
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Container_Fieldset extends HTML_QuickForm2_Container
{
   /**
    * Fieldsets don't have a 'name' attribute, so we only handle 'id'
    * @var array
    */
    protected $watchedAttributes = array('id');

    public function getType()
    {
        return 'fieldset';
    }


    public function getName()
    {
        return null;
    }


    public function setName($name)
    {
        // Fieldsets do not have a name attribute
        return $this;
    }


    public function setValue($value)
    {
        throw new HTML_QuickForm2_Exception('Not implemented');
    }
}
?>