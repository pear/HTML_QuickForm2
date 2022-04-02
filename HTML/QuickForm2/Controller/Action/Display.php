<?php
/**
 * Action handler for outputting the form
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

// pear-package-only /** Interface for Controller action handlers */
// pear-package-only require_once 'HTML/QuickForm2/Controller/Action.php';

// pear-package-only /** Class presenting the values stored in session by Controller as submitted ones */
// pear-package-only require_once 'HTML/QuickForm2/DataSource/Session.php';

/**
 * Action handler for outputting the form
 *
 * If you want to customize the form display, subclass this class and override
 * the renderForm() method, you don't need to change the perform() method.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Controller_Action_Display
    implements HTML_QuickForm2_Controller_Action
{
    public function perform(HTML_QuickForm2_Controller_Page $page, $name)
    {
        $validate        = false;
        $datasources     = $page->getForm()->getDataSources();
        $container       = $page->getController()->getSessionContainer();
        list(, $oldName) = $page->getController()->getActionName();
        // Check the original action name, we need to do additional processing
        // if it was 'display'
        if ('display' == $oldName) {
            // In case of wizard-type controller we should not allow access to
            // a page unless all previous pages are valid (see also bug #2323)
            if ($page->getController()->isWizard()
                && !$page->getController()->isValid($page)
            ) {
                /** @psalm-suppress PossiblyNullReference */
                return $page->getController()->getFirstInvalidPage()->handle('jump');
            }
            // If we have values in container then we should inject the Session
            // DataSource, if page was invalid previously we should later call
            // validate() to get the errors
            if (count($container->getValues($page->getForm()->getId()))) {
                array_unshift($datasources, new HTML_QuickForm2_DataSource_Session(
                    $container->getValues($page->getForm()->getId())
                ));
                $validate = false === $container->getValidationStatus($page->getForm()->getId());
            }
        }

        // Add "defaults" datasources stored in session
        $page->getForm()->setDataSources(array_merge($datasources, $container->getDatasources()));
        $page->populateFormOnce();
        if ($validate) {
            $page->getForm()->validate();
        }
        return $this->renderForm($page->getForm());
    }

   /**
    * Outputs the form
    *
    * Default behaviour is to rely on form's __toString() magic method.
    * If you want to customize form appearance or use a different Renderer,
    * you should override this method.
    *
    * @param HTML_QuickForm2 $form
    */
    protected function renderForm(HTML_QuickForm2 $form)
    {
        echo $form;
    }
}
?>
