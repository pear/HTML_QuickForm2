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
 * @copyright 2006-2022 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */


/**
 * Exception that denotes some resource was not found
 *
 * One example is trying to instantiate a nonexistent class in Factory
 * <code>
 * try {
 *     HTML_QuickForm2_Factory::registerElement('missing', 'NonExistent');
 *     $el = HTML_QuickForm2_Factory::createElement('missing');
 * } catch (HTML_QuickForm2_NotFoundException $e) {
 *     echo $e->getMessage();
 * }
 * </code>
 * This code fill output "Class 'NonExistent' does not exist and no file to load"
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_NotFoundException extends HTML_QuickForm2_Exception
{
}
?>