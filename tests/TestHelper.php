<?php
/**
 * Unit tests for HTML_QuickForm2 package
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

// We need to be sure that we have sane dependencies (HTML_Common2 and PEAR_Exception)
// So we only allow the tests to run when installed with PEAR or with composer

$installed = false;

if ('@' . 'package_version@' !== '@package_version@') {
    // Installed with PEAR: we should be on the include path, use own autoloader
    require_once 'HTML/QuickForm2/Loader.php';
    spl_autoload_register(array('HTML_QuickForm2_Loader', 'autoload'));
    $installed = true;

} else {
    foreach (array(dirname(__FILE__) . '/../../../autoload.php', dirname(__FILE__) . '/../vendor/autoload.php') as $file) {
        if (file_exists($file)) {
            require_once $file;
            $installed = true;

            break;
        }
    }

}

if (!$installed) {
    fwrite(STDERR,
        'As HTML_QuickForm2 has required dependencies, tests should be run either' . PHP_EOL . PHP_EOL .
        ' - after installation of package with PEAR:' . PHP_EOL .
        '    php ./pear-package-helper.php' . PHP_EOL .
        '    pear install ./.pear-package/package.xml' . PHP_EOL . PHP_EOL .
        ' - or setting up its dependencies using Composer:' . PHP_EOL .
        '    composer install' . PHP_EOL . PHP_EOL
    );

    die(1);
}



if (strpos($_SERVER['argv'][0], 'phpunit') === false
    && !class_exists('PHPUnit_Framework_TestCase', true)
) {
    require_once 'PHPUnit/Autoload.php';
}
?>