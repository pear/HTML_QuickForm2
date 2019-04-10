<?php
/**
 * Provides lists of months and weekdays for date elements using current locale
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

// pear-package-only /** Interface for classes that supply (translated) messages for the elements */
// pear-package-only require_once 'HTML/QuickForm2/MessageProvider.php';

/**
 * Provides lists of months and weekdays for date elements using current locale
 *
 * Uses locale-aware strftime() formatting function. The class does not try to
 * do anything with locale itself, so be sure to set it up properly before
 * adding date elements to the form.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 * @link     http://pear.php.net/bugs/bug.php?id=5558
 */
class HTML_QuickForm2_MessageProvider_Strftime implements HTML_QuickForm2_MessageProvider
{
   /**
    * Lists of month names and weekdays accorfing to current locale
    * @var array
    */
    protected $messages = array(
        'weekdays_short'=> array(),
        'weekdays_long' => array(),
        'months_short'  => array(),
        'months_long'   => array()
    );

   /**
    * Constructor, builds lists of month and weekday names
    */
    public function __construct()
    {
        for ($i = 1; $i <= 12; $i++) {
            $names = explode("\n", strftime("%b\n%B", mktime(12, 0, 0, $i, 1, 2011)));
            $this->messages['months_short'][] = $names[0];
            $this->messages['months_long'][]  = $names[1];
        }
        for ($i = 0; $i < 7; $i++) {
            $names = explode("\n", strftime("%a\n%A", mktime(12, 0, 0, 1, 2 + $i, 2011)));
            $this->messages['weekdays_short'][] = $names[0];
            $this->messages['weekdays_long'][]  = $names[1];
        }
    }

   /**
    * Returns name(s) of months and weekdays for date elements
    *
    * @param array  $messageId Message ID
    * @param string $langId    Not used, current locale will define the language
    *
    * @return   array|string|null
    * @throws   HTML_QuickForm2_InvalidArgumentException if $messageId doesn't
    *               start with 'date'
    */
    public function get(array $messageId, $langId = null)
    {
        if ('date' != array_shift($messageId)) {
            throw new HTML_QuickForm2_InvalidArgumentException('...');
        }

        $message = $this->messages;
        while (!empty($messageId)) {
            $key = array_shift($messageId);
            if (!isset($message[$key])) {
                return null;
            }
            $message = $message[$key];
        }
        return $message;
    }
}
?>
