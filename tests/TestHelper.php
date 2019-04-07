<?php
/**
 * Unit tests for HTML_QuickForm2 package
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2014, Alexey Borzov <avb@php.net>,
 *                          Bertrand Mansion <golgote@mamasam.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://pear.php.net/package/HTML_QuickForm2
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