<?php

require_once 'HTML/QuickForm2/MessageProvider.php';

class HTML_QuickForm2_MessageProvider_Strftime implements HTML_QuickForm2_MessageProvider
{
    protected $messages = array(
        'weekdays_short'=> array(),
        'weekdays_long' => array(),
        'months_short'  => array(),
        'months_long'   => array()
    );

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

    public function get($messageId, $langId = null)
    {
        if (!is_array($messageId)) {
            $messageId = array($messageId);
        }
        $key = array_shift($messageId);
        if ('date' != $key) {
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