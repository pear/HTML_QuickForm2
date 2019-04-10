<?php
/**
 * Action handler for a 'submit' button
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

// pear-package-only /** Interface for Controller action handlers */
// pear-package-only require_once 'HTML/QuickForm2/Controller/Action.php';

/**
 * Action handler for a 'submit' button
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Controller_Action_Submit
    implements HTML_QuickForm2_Controller_Action
{
    public function perform(HTML_QuickForm2_Controller_Page $page, $name)
    {
        $valid = $page->storeValues();

        // All pages are valid, process
        if ($page->getController()->isValid()) {
            return $page->handle('process');

        // Current page is invalid, display it
        } elseif (!$valid) {
            return $page->handle('display');

        // Some other page is invalid, redirect to it
        } else {
            return $page->getController()->getFirstInvalidPage()->handle('jump');
        }
    }
}
?>
