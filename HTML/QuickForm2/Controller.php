<?php
/**
 * Class implementing the Page Controller pattern for multipage forms
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

// pear-package-only /** The class representing a page of a multipage form */
// pear-package-only require_once 'HTML/QuickForm2/Controller/Page.php';

// pear-package-only /** Object wrapping around session variable used to store controller data */
// pear-package-only require_once 'HTML/QuickForm2/Controller/SessionContainer.php';

// pear-package-only /** Class presenting the values stored in session by Controller as submitted ones */
// pear-package-only require_once 'HTML/QuickForm2/DataSource/Session.php';

/**
 * Class implementing the Page Controller pattern for multipage forms
 *
 * This class keeps track of pages and (default) action handlers for the form,
 * it manages $_SESSION container for the form values, allows setting
 * DataSources for the form as a whole and getting its value.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 *
 * @implements IteratorAggregate<string, HTML_QuickForm2_Controller_Page>
 */
class HTML_QuickForm2_Controller implements IteratorAggregate
{
   /**
    * Key in $_REQUEST array that contains the ID of the Controller
    */
    const KEY_ID = '_qfc_id';

   /**
    * Key in $_SESSION array that contains the Controller data (needs ID substituted via sprintf())
    */
    const KEY_CONTAINER = '_%s_container';

   /**
    * Whether the form is a wizard
    * @var  boolean
    */
    protected $wizard = true;

   /**
    * Whether Controller ID should be sent in GET and POST parameters
    * @var  boolean
    */
    protected $propagate = true;

   /**
    * Controller ID
    * @var string
    */
    protected $id;

   /**
    * Contains the pages (instances of HTML_QuickForm2_Controller_Page) of the multipage form
    * @var array<string, HTML_QuickForm2_Controller_Page>
    */
    protected $pages = [];

   /**
    * Contains the mapping of action names to handlers (objects implementing HTML_QuickForm2_Controller_Action)
    * @var array<string, HTML_QuickForm2_Controller_Action>
    */
    protected $handlers = [];

   /**
    * The action extracted from HTTP request: array('page', 'action')
    * @var array|null
    */
    protected $actionName = null;

   /**
    * A wrapper around session variable used to store form data
    * @var HTML_QuickForm2_Controller_SessionContainer|null
    */
    protected $sessionContainer = null;

   /**
    * Finds a controller name in $_REQUEST
    *
    * @return string|null   Returns nulle if either a KEY_ID is not present
    *                       in $_REQUEST or KEY_CONTAINER is not present in
    *                       $_SESSION
    */
    public static function findControllerID()
    {
        if (empty($_REQUEST[self::KEY_ID])
            || !is_string($_REQUEST[self::KEY_ID])
            || empty($_SESSION[sprintf(self::KEY_CONTAINER, $_REQUEST[self::KEY_ID])])
        ) {
            return null;
        } else {
            return $_REQUEST[self::KEY_ID];
        }
    }

   /**
    * Class constructor
    *
    * Sets the form ID, whether to send this ID in POST and GET parameters,
    * wizard / non-wizard behaviour.
    *
    * Different forms should be given different IDs, as they are used to store
    * values in session. If $id is empty, the controller will try to find it
    * in $_REQUEST, throwing the exception if this fails.
    *
    * Wizard forms only allow going to the next page if all the previous ones
    * are valid.
    *
    * @param string  $id          Form ID
    * @param boolean $wizard      Whether the form is a wizard
    * @param boolean $propagateId Whether form's ID should be sent with
    *                             GET and POST parameters
    *
    * @throws   HTML_QuickForm2_NotFoundException   if ID is not given and cannot
    *               be found in $_REQUEST, or session container is empty
    */
    public function __construct($id = null, $wizard = true, $propagateId = false)
    {
        if (empty($id)) {
            $propagateId = true;
            $id          = self::findControllerID();
        }
        if (empty($id)) {
            throw new HTML_QuickForm2_NotFoundException(
                'Controller ID not available in $_REQUEST or session ' .
                'container is empty, please provide ID to constructor'
            );
        }
        $this->id        = $id;
        $this->wizard    = (bool)$wizard;
        $this->propagate = (bool)$propagateId;
    }

   /**
    * Returns whether the form is a wizard
    *
    * @return   boolean
    */
    public function isWizard()
    {
        return $this->wizard;
    }

   /**
    * Returns the form ID
    *
    * @return   string
    */
    public function getId()
    {
        return $this->id;
    }

   /**
    * Returns whether to send form id with GET and POST parameters
    *
    * @return   boolean
    */
    public function propagateId()
    {
        return $this->propagate;
    }

   /**
    * Returns the session container with the controller data
    *
    * @return   HTML_QuickForm2_Controller_SessionContainer
    */
    public function getSessionContainer()
    {
        if (empty($this->sessionContainer)) {
            $this->sessionContainer = new HTML_QuickForm2_Controller_SessionContainer($this);
        }
        return $this->sessionContainer;
    }

   /**
    * Removes the session variable containing the controller data
    */
    public function destroySessionContainer()
    {
        unset($_SESSION[sprintf(self::KEY_CONTAINER, $this->id)]);
        $this->sessionContainer = null;
    }

   /**
    * Extracts the name of the page and the action to perform with it from HTTP request data
    *
    * @return array     first element is page name, second is action name
    */
    public function getActionName()
    {
        if (is_array($this->actionName)) {
            return $this->actionName;
        }
        if (empty($this->pages)) {
            throw new HTML_QuickForm2_NotFoundException('No pages added to the form');
        }
        $names = array_map('preg_quote', array_keys($this->pages));
        $regex = '/^_qf_(' . implode('|', $names) . ')_(.+?)(_x)?$/';
        foreach (array_keys($_REQUEST) as $key) {
            if (preg_match($regex, (string)$key, $matches)) {
                $this->actionName = [$matches[1], $matches[2]];
                break;
            }
        }
        if (!is_array($this->actionName)) {
            reset($this->pages);
            $this->actionName = [key($this->pages), 'display'];
        }
        return $this->actionName;
    }

   /**
    * Processes the request
    *
    * This finds the page, the action to perform with it and passes the action
    * to the page's handle() method.
    *
    * @return mixed Return value of action handler
    * @throws HTML_QuickForm2_Exception
    */
    public function run()
    {
        list($page, $action) = $this->getActionName();
        return $this->pages[$page]->handle($action);
    }

   /**
    * Adds a handler for a specific action
    *
    * @param string                            $actionName action name
    * @param HTML_QuickForm2_Controller_Action $action     the handler for the action
    */
    public function addHandler($actionName, HTML_QuickForm2_Controller_Action $action)
    {
        $this->handlers[$actionName] = $action;
    }

   /**
    * Handles an action
    *
    * This will be called if the page itself does not have a handler for a
    * specific action. The method also loads and uses default handlers for
    * common actions, if specific ones were not added.
    *
    * @param HTML_QuickForm2_Controller_Page $page       form page
    * @param string                          $actionName action name
    *
    * @return mixed Return value of action handler
    * @throws HTML_QuickForm2_NotFoundException   if handler for an action is missing
    */
    public function handle(HTML_QuickForm2_Controller_Page $page, $actionName)
    {
        if (!isset($this->handlers[$actionName])
            && in_array($actionName, ['next', 'back', 'submit', 'display', 'jump'])
        ) {
            /** @var class-string<HTML_QuickForm2_Controller_Action> $className */
            $className = 'HTML_QuickForm2_Controller_Action_' . ucfirst($actionName);
            // pear-package-only HTML_QuickForm2_Loader::loadClass($className);
            $this->addHandler($actionName, new $className());
        }
        if (isset($this->handlers[$actionName])) {
            return $this->handlers[$actionName]->perform($page, $actionName);
        } else {
            throw new HTML_QuickForm2_NotFoundException(
                "Unhandled action '{$actionName}' for page '{$page->getForm()->getId()}'"
            );
        }
    }

   /**
    * Adds a new page to the form
    *
    * @param HTML_QuickForm2_Controller_Page $page
    */
    public function addPage(HTML_QuickForm2_Controller_Page $page)
    {
        $pageId = $page->getForm()->getId();
        if (!empty($this->pages[$pageId])) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Duplicate page ID '{$pageId}'"
            );
        }
        $page->setController($this);
        $this->pages[$pageId] = $page;
    }

   /**
    * Returns a page
    *
    * @param string $pageId Page ID
    *
    * @return HTML_QuickForm2_Controller_Page
    * @throws HTML_QuickForm2_NotFoundException if there is no page with
    *           the given ID
    */
    public function getPage($pageId)
    {
        if (!empty($this->pages[$pageId])) {
            return $this->pages[$pageId];
        } else {
            throw new HTML_QuickForm2_NotFoundException(
                "Unknown page '{$pageId}'"
            );
        }
    }

   /**
    * Returns the page preceding the given one
    *
    * @param HTML_QuickForm2_Controller_Page $reference
    *
    * @return   HTML_QuickForm2_Controller_Page|null
    */
    public function previousPage(HTML_QuickForm2_Controller_Page $reference)
    {
        $previous = null;
        foreach ($this->pages as $page) {
            if ($page === $reference) {
                return $previous;
            }
            $previous = $page;
        }
        return null;
    }

   /**
    * Returns the page following the given one
    *
    * @param HTML_QuickForm2_Controller_Page $reference
    *
    * @return   HTML_QuickForm2_Controller_Page|null
    */
    public function nextPage(HTML_QuickForm2_Controller_Page $reference)
    {
        $previous = null;
        foreach ($this->pages as $page) {
            if ($previous === $reference) {
                return $page;
            }
            $previous = $page;
        }
        return null;
    }

   /**
    * Checks whether the pages of the controller are valid
    *
    * @param HTML_QuickForm2_Controller_Page $reference If given, check only
    *                               the pages before (not including) that page
    *
    * @return bool
    */
    public function isValid(HTML_QuickForm2_Controller_Page $reference = null)
    {
        $container = $this->getSessionContainer();
        foreach ($this->pages as $id => $page) {
            if ($reference === $page) {
                return true;
            }
            if (!$container->getValidationStatus($id)) {
                // We should handle the possible situation when the user has never
                // seen a page of a non-modal multipage form
                if (!$this->isWizard()
                    && null === $container->getValidationStatus($id)
                ) {
                    // Empty Session datasource makes the form look submitted
                    $page->getForm()->setDatasources(array_merge(
                        $container->getDatasources(),
                        [new HTML_QuickForm2_DataSource_Session([])]
                    ));
                    // This will store the "submitted" values in session and
                    // return validation status
                    if ($page->storeValues()) {
                        continue;
                    }
                }
                return false;
            }
        }
        return true;
    }

   /**
    * Returns the first page that failed validation
    *
    * @return   HTML_QuickForm2_Controller_Page|null
    */
    public function getFirstInvalidPage()
    {
        foreach ($this->pages as $id => $page) {
            if (!$this->getSessionContainer()->getValidationStatus($id)) {
                return $page;
            }
        }
        return null;
    }

   /**
    * Adds a new data source to the Controller
    *
    * Note that Controller data sources are stored in session, so your data source
    * implementation should properly handle its (un)serialization.
    *
    * @param HTML_QuickForm2_DataSource $datasource Data source
    */
    public function addDataSource(HTML_QuickForm2_DataSource $datasource)
    {
        $this->getSessionContainer()->storeDatasources(
            array_merge(
                $this->getSessionContainer()->getDatasources(), [$datasource]
            )
        );
    }

   /**
    * Returns the form's values
    *
    * @return   array
    */
    public function getValue()
    {
        $values = [];
        foreach (array_keys($this->pages) as $id) {
            $values = HTML_QuickForm2_Container::arrayMerge(
                $values, $this->getSessionContainer()->getValues($id)
            );
        }
        return $values;
    }

    #[ReturnTypeWillChange]
   /**
    * Returns an Iterator for the form's pages
    *
    * @return   ArrayIterator
    */
    public function getIterator()
    {
        return new ArrayIterator($this->pages);
    }
}
?>