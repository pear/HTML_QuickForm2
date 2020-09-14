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
 * @copyright 2006-2020 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

/** Sets up includes */
require_once dirname(dirname(dirname(__DIR__))) . '/TestHelper.php';

/**
 * Unit test for HTML_QuickForm2_Controller_Action_Jump class
 */
class HTML_QuickForm2_Controller_Action_JumpTest
    extends PHPUnit_Framework_TestCase
{
    protected $mockJump;

    public function setUp()
    {
        $this->mockJump = $this->getMockBuilder('HTML_QuickForm2_Controller_Action_Jump')
            ->setMethods(['doRedirect'])
            ->getMock();
        $this->mockJump->expects($this->atLeastOnce())->method('doRedirect')
             ->will($this->returnArgument(0));

        // see RFC 3986, section 5.4
        $_SERVER['HTTP_HOST']   = 'a';
        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['REQUEST_URI'] = '/b/c/d;p?q';
    }

   /**
    * Requirement of RFC 2616, section 14.30
    *
    * @link http://pear.php.net/bugs/bug.php?id=13087
    */
    public function testRedirectToAbsoluteUrl()
    {
        // Examples from RFC 3986 section 5.4, except those with fragments
        $rfc3986tests = [
            ""              =>  "http://a/b/c/d;p?q",
            "g:h"           =>  "g:h",
            "g"             =>  "http://a/b/c/g",
            "./g"           =>  "http://a/b/c/g",
            "g/"            =>  "http://a/b/c/g/",
            "/g"            =>  "http://a/g",
            "//g"           =>  "http://g",
            "?y"            =>  "http://a/b/c/d;p?y",
            "g?y"           =>  "http://a/b/c/g?y",
            ";x"            =>  "http://a/b/c/;x",
            "g;x"           =>  "http://a/b/c/g;x",
            ""              =>  "http://a/b/c/d;p?q",
            "."             =>  "http://a/b/c/",
            "./"            =>  "http://a/b/c/",
            ".."            =>  "http://a/b/",
            "../"           =>  "http://a/b/",
            "../g"          =>  "http://a/b/g",
            "../.."         =>  "http://a/",
            "../../"        =>  "http://a/",
            "../../g"       =>  "http://a/g",
            "../../../g"    =>  "http://a/g",
            "../../../../g" =>  "http://a/g",
            "/./g"          =>  "http://a/g",
            "/../g"         =>  "http://a/g",
            "g."            =>  "http://a/b/c/g.",
            ".g"            =>  "http://a/b/c/.g",
            "g.."           =>  "http://a/b/c/g..",
            "..g"           =>  "http://a/b/c/..g",
            "./../g"        =>  "http://a/b/g",
            "./g/."         =>  "http://a/b/c/g/",
            "g/./h"         =>  "http://a/b/c/g/h",
            "g/../h"        =>  "http://a/b/c/h",
            "g;x=1/./y"     =>  "http://a/b/c/g;x=1/y",
            "g;x=1/../y"    =>  "http://a/b/c/y",
            "g?y/./x"       =>  "http://a/b/c/g?y/./x",
            "g?y/../x"      =>  "http://a/b/c/g?y/../x",
            "http:g"        =>  "http:g",
        ];

        $controller = new HTML_QuickForm2_Controller('rfc3986', true);
        $mockPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(['populateForm'])
            ->setConstructorArgs([new HTML_QuickForm2('relative')])
            ->getMock();
        $mockPage->addHandler('jump', $this->mockJump);
        $controller->addPage($mockPage);

        foreach ($rfc3986tests as $relative => $absolute) {
            $mockPage->getForm()->setAttribute('action', $relative);
            $this->assertEquals($absolute, preg_replace('/[&?]_qf(.*)$/', '', $mockPage->handle('jump')));
        }
    }

    public function testCannotRedirectPastInvalidPageInWizard()
    {
        $controller = new HTML_QuickForm2_Controller('twopagewizard', true);
        $controller->addPage(
            $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
                ->setMethods(['populateForm'])
                ->setConstructorArgs([new HTML_QuickForm2('first')])
                ->getMock()
        );
        $controller->addPage(
            $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
                ->setMethods(['populateForm'])
                ->setConstructorArgs([new HTML_QuickForm2('second')])
                ->getMock()
        );
        $controller->addHandler('jump', $this->mockJump);

        $this->assertContains(
            $controller->getPage('first')->getButtonName('display'),
            $controller->getPage('second')->handle('jump')
        );
    }

    public function testPropagateControllerId()
    {
        $noPropPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(['populateForm'])
            ->setConstructorArgs([new HTML_QuickForm2('noPropagateForm')])
            ->getMock();
        $noPropController = new HTML_QuickForm2_Controller('foo', true, false);
        $noPropController->addPage($noPropPage);
        $noPropController->addHandler('jump', $this->mockJump);
        $this->assertNotContains(
            HTML_QuickForm2_Controller::KEY_ID . '=',
            $noPropPage->handle('jump')
        );

        $propPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(['populateForm'])
            ->setConstructorArgs([new HTML_QuickForm2('propagateForm')])
            ->getMock();
        $propController = new HTML_QuickForm2_Controller('bar', true, true);
        $propController->addPage($propPage);
        $propController->addHandler('jump', $this->mockJump);
        $this->assertContains(
            HTML_QuickForm2_Controller::KEY_ID . '=bar',
            $propPage->handle('jump')
        );
    }

   /**
    * Do not add session ID to redirect URL when session.use_only_cookies is set
    *
    * @link http://pear.php.net/bugs/bug.php?id=3443
    */
    public function testBug3443()
    {
        if ('' != session_id()) {
            $this->markTestSkipped('This test cannot be run with session started');
        }
        if (version_compare(phpversion(), '7.2', '>=')) {
            $this->markTestSkipped('Cannot change session config in PHP 7.2+ after headers are sent');
        }

        $old = ini_set('session.use_only_cookies', false);
        define('SID', 'mysessionid=foobar');

        $controller = new HTML_QuickForm2_Controller('testBug3443');
        $controller->addPage(
            $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
                ->setMethods(['populateForm'])
                ->setConstructorArgs([new HTML_QuickForm2('dest')])
                ->getMock()
        );
        $controller->addHandler('jump', $this->mockJump);
        $this->assertContains('mysessionid=', $controller->getPage('dest')->handle('jump'));

        ini_set('session.use_only_cookies', true);
        $this->assertNotContains('mysessionid=', $controller->getPage('dest')->handle('jump'));

        ini_set('session.use_only_cookies', $old);
    }

   /**
    * Uppercase 'OFF' in $_SERVER['HTTPS'] could cause a bogus redirect to https:// URL
    *
    * @link http://pear.php.net/bugs/bug.php?id=16328
    */
    public function testBug16328()
    {
        $_SERVER['HTTPS'] = 'OFF';

        $controller = new HTML_QuickForm2_Controller('bug16328');
        $mockPage   = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(['populateForm'])
            ->setConstructorArgs([new HTML_QuickForm2('unsecure')])
            ->getMock();
        $controller->addPage($mockPage);
        $controller->addHandler('jump', $this->mockJump);
        $mockPage->getForm()->setAttribute('action', '/foo');

        $this->assertNotRegexp('/^https:/i', $mockPage->handle('jump'));
    }

   /**
    * Use HTTP_HOST as the default, falling back to SERVER_NAME (and SERVER_ADDR)
    *
    * @link http://pear.php.net/bugs/bug.php?id=19216
    */
    public function testBug19216()
    {
        $controller = new HTML_QuickForm2_Controller('bug19216');
        $mockPage   = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(['populateForm'])
            ->setConstructorArgs([new HTML_QuickForm2('testhost')])
            ->getMock();
        $controller->addPage($mockPage);
        $controller->addHandler('jump', $this->mockJump);
        $mockPage->getForm()->setAttribute('action', '/foo');

        $_SERVER['HTTP_HOST']   = 'example.org';
        $_SERVER['SERVER_NAME'] = 'example.com';
        $_SERVER['SERVER_ADDR'] = '1.2.3.4';
        $this->assertStringStartsWith('http://example.org/foo?', $mockPage->handle('jump'));

        $_SERVER['HTTP_HOST'] = '';
        $this->assertStringStartsWith('http://example.com/foo?', $mockPage->handle('jump'));

        $_SERVER['SERVER_NAME'] = '';
        $this->assertStringStartsWith('http://1.2.3.4/foo?', $mockPage->handle('jump'));
    }

    public function testHttpHostWithPortNumber()
    {
        $controller = new HTML_QuickForm2_Controller('weirdhost');
        $mockPage   = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(['populateForm'])
            ->setConstructorArgs([new HTML_QuickForm2('weirdhost')])
            ->getMock();
        $controller->addPage($mockPage);
        $controller->addHandler('jump', $this->mockJump);
        $mockPage->getForm()->setAttribute('action', '/foo');

        $_SERVER['HTTP_HOST'] = 'example.org:80';
        $this->assertStringStartsWith('http://example.org/foo?', $mockPage->handle('jump'));
    }

    public function testHttpXForwardedHost()
    {
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'example.org, example.com';
        $_SERVER['HTTP_HOST']             = 'localhost';

        $controller = new HTML_QuickForm2_Controller('forwarded');
        $mockPage   = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(['populateForm'])
            ->setConstructorArgs([new HTML_QuickForm2('forwarded')])
            ->getMock();
        $controller->addPage($mockPage);
        $controller->addHandler('jump', $this->mockJump);
        $mockPage->getForm()->setAttribute('action', '/foo');

        $this->assertStringStartsWith('http://localhost/foo?', $mockPage->handle('jump'));

        $trustingJump = $this->getMockBuilder('HTML_QuickForm2_Controller_Action_Jump')
            ->setMethods(['doRedirect'])
            ->setConstructorArgs([true])
            ->getMock();
        $trustingJump->expects($this->atLeastOnce())->method('doRedirect')
            ->will($this->returnArgument(0));
        $controller->addHandler('jump', $trustingJump);

        $this->assertStringStartsWith('http://example.com/foo?', $mockPage->handle('jump'));
    }
}
?>