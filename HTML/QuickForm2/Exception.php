<?php
/**
 * Exception classes for HTML_QuickForm2
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
// pear-package-only  * Base class for exceptions in PEAR
// pear-package-only  */
// pear-package-only require_once 'PEAR/Exception.php';

/**
 * Base class for exceptions in HTML_QuickForm2 package
 *
 * Such a base class is required by the Exception RFC:
 * http://pear.php.net/pepr/pepr-proposal-show.php?id=132
 * It will rarely be thrown directly, its specialized subclasses will be
 * thrown most of the time.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Exception extends PEAR_Exception
{
}

// backwards compatibility, include the child exceptions if installed with PEAR installer
// pear-package-only require_once 'HTML/QuickForm2/InvalidArgumentException.php';
// pear-package-only require_once 'HTML/QuickForm2/NotFoundException.php';
?>
