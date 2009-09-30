<?php
/**
 * Usage example for HTML_QuickForm2 package: builtin rules
 *
 * The example uses all Rule classes provided with HTML_QuickForm2 and also
 * showcases rule chaining.
 *
 * $Id$
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
  <head>
    <style type="text/css">
/* Set up custom font and form width */
body {
    margin-left: 10px;
    font-family: Arial,sans-serif;
    font-size: small;
}

.quickform {
    min-width: 500px;
    max-width: 600px;
    width: 560px;
}

/* Use default styles included with the package */

<?php
if ('@data_dir@' != '@' . 'data_dir@') {
    $filename = '@data_dir@/HTML_QuickForm2/data/quickform.css';
} else {
    $filename = dirname(dirname(dirname(__FILE__))) . '/data/quickform.css';
}
readfile($filename);
?>
    </style>
    <title>HTML_QuickForm2 basic elements example</title>
  </head>
  <body>
<?php

// in real application the password check will a bit be different, of course
function check_password($password)
{
    return ($password == 'qwerty');
}

//
// Form setup
//

require_once 'HTML/QuickForm2.php';

$form = new HTML_QuickForm2('basicRules');
// for file upload to work
$form->setAttribute('enctype', 'multipart/form-data');

// data source with default values:
$form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
    'testUsername'      => 'luser'
)));

// override whatever value was submitted
$form->addElement('hidden', 'MAX_FILE_SIZE')->setValue('102400');

//
// Simple fields validation, rule chaining
//

$fsAuth = $form->addElement('fieldset')->setLabel('Auth credentials');
$username = $fsAuth->addElement('text', 'testUsername', array('style' => 'width: 200px;'))
                   ->setLabel('Username (letters only):');

$fsPasswords = $fsAuth->addElement('fieldset')
                      ->setLabel('Supply password only if you want to change it');

$oldPassword = $fsPasswords->addElement('password', 'oldPassword', array('style' => 'width: 200px;'))
                           ->setLabel('Old password (<i>qwerty</i>):');
$newPassword = $fsPasswords->addElement('password', 'newPassword', array('style' => 'width: 200px;'))
                           ->setLabel('New password (min 6 chars):');
$repPassword = $fsPasswords->addElement('password', 'newPasswordRepeat', array('style' => 'width: 200px;'))
                           ->setLabel('Repeat new password:');

$username->addRule('required', 'Username is required');
$username->addRule('regex', 'Username should contain only letters', '/^[a-zA-Z]+$/');

// old password should be either left blank or be equal to 'qwerty'
$oldPassword->addRule('empty')
            ->or_($oldPassword->createRule('callback', 'Wrong password', 'check_password'));

// this behaves exactly as it reads: either "password" and "password repeat"
// are empty or they should be equal, password should be no less than 6 chars
// and old password should be given
$newPassword->addRule('empty')
            ->and_($repPassword->createRule('empty'))
            ->or_($repPassword->createRule('eq', 'The passwords do not match', $newPassword))
            ->and_($newPassword->createRule('minlength', 'The password is too short', 6))
            ->and_($oldPassword->createRule('nonempty', 'Supply old password if you want to change it'));

//
// File uploads validation
//

$fsUpload = $form->addElement('fieldset')->setLabel('Upload picture (try one &gt; 100 kB for fun)');
$upload = $fsUpload->addElement('file', 'testUpload', array('style' => 'width: 200px'))
                   ->setLabel('Picture (gif, jpg, png, &lt;=20kB):');

// no longer using special 'uploadedfile' rule for uploads
$upload->addRule('required', 'Please upload picture');
// no longer using 'filename' rule for uploads
$upload->addRule('regex', 'Allowed extensions: .gif, .jp(e)g, .png', '/\\.(gif|jpe?g|png)$/i');
$upload->addRule('mimetype', 'Your browser doesn\'t think that\'s an image',
                 array('image/gif', 'image/png', 'image/jpeg', 'image/pjpeg'));
$upload->addRule('maxfilesize', 'File is too big, allowed size 20kB', 20480);

$form->addElement('submit', 'testSubmit', array('value' => 'Send'));

if ($form->validate()) {
    echo "<pre>\n";
    var_dump($form->getValue());
    echo "</pre>\n<hr />";
    $form->toggleFrozen(true);
}

echo $form;
?>
  </body>
</html>