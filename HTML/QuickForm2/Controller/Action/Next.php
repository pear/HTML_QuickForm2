<?php
/**
 * Action handler for a 'next' button of wizard-type multipage form
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
 * @copyright 2006-2020 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

// pear-package-only /** Interface for Controller action handlers */
// pear-package-only require_once 'HTML/QuickForm2/Controller/Action.php';

/**
 * Action handler for a 'next' button of wizard-type multipage form
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Controller_Action_Next
    implements HTML_QuickForm2_Controller_Action
{
    public function perform(HTML_QuickForm2_Controller_Page $page, $name)
    {
        $valid = $page->storeValues();

        // Wizard and page is invalid: don't go further
        if ($page->getController()->isWizard() && !$valid) {
            return $page->handle('display');
        }

        // More pages?
        if (null !== ($next = $page->getController()->nextPage($page))) {
            return $next->handle('jump');

        // Consider this a 'finish' button, if there is no explicit one
        } elseif ($page->getController()->isWizard()) {
            if ($page->getController()->isValid()) {
                return $page->handle('process');
            } else {
                // redirect to the first invalid page
                return $page->getController()->getFirstInvalidPage()->handle('jump');
            }

        } else {
            return $page->handle('display');
        }
    }
}
?>
