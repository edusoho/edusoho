<?php

namespace Tests\Unit\AppBundle\Common;

use AppBundle\Common\DeviceToolkit;
use Biz\BaseTestCase;

class DeviceToolkitTest extends BaseTestCase
{
    public function testisMobileClient()
    {
        $_SERVER = array_merge($_SERVER, array(
            'HTTP_X_WAP_PROFILE' => true,
            'HTTP_VIA' => 'wap',
            'HTTP_USER_AGENT' => 'nokia',
            'HTTP_ACCEPT' => 'vnd.wap.wml',
        ));

        $this->assertTrue(DeviceToolkit::isMobileClient());

        unset($_SERVER['HTTP_X_WAP_PROFILE']);
        $this->assertTrue(DeviceToolkit::isMobileClient());

        unset($_SERVER['HTTP_VIA']);
        $this->assertTrue(DeviceToolkit::isMobileClient());

        unset($_SERVER['HTTP_USER_AGENT']);
        $this->assertTrue(DeviceToolkit::isMobileClient());

        unset($_SERVER['HTTP_ACCEPT']);
        $this->assertFalse(DeviceToolkit::isMobileClient());
    }

    public function testIsIOSClient()
    {
        $_SERVER = array_merge($_SERVER, array(
            'HTTP_USER_AGENT' => 'IPHONE',
        ));

        $this->assertTrue(DeviceToolkit::isIOSClient());

        unset($_SERVER['HTTP_USER_AGENT']);
        $this->assertFalse(DeviceToolkit::isIOSClient());
    }

    public function testGetMobileDeviceType()
    {
        $this->assertEquals('ios', DeviceToolkit::getMobileDeviceType('IPhone'));

        $this->assertEquals('android', DeviceToolkit::getMobileDeviceType('Android'));

        $this->assertEquals('unknown', DeviceToolkit::getMobileDeviceType('test'));
    }

    public function testGetBrowse()
    {
        $this->assertEquals('未知浏览器', DeviceToolkit::getBrowse());
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:56.0) Gecko/20100101 Firefox/56.0';

        $this->assertEquals('Firefox(56.0)', DeviceToolkit::getBrowse());

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0';
        $this->assertEquals('IE(9.0)', DeviceToolkit::getBrowse());

        $_SERVER['HTTP_USER_AGENT'] = 'Opera/9.80 (Windows NT 6.1; U; en) Presto/2.8.131 Version/11.11';
        $this->assertEquals('Opera(9.80)', DeviceToolkit::getBrowse());

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36 Edge/15.15063';
        $this->assertEquals('Edge(15.15063)', DeviceToolkit::getBrowse());

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.119 Safari/537.36';
        $this->assertEquals('Chrome(72.0.3626.119)', DeviceToolkit::getBrowse());

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0.1 Safari/604.3.5';
        $this->assertEquals('Safari(604.3.5)', DeviceToolkit::getBrowse());

        $_SERVER['HTTP_USER_AGENT'] = 'test agent';
        $this->assertEquals('未知浏览器()', DeviceToolkit::getBrowse());

        unset($_SERVER['HTTP_USER_AGENT']);
        $this->assertEquals('未知浏览器', DeviceToolkit::getBrowse());
    }

    public function testGetOperatingSystem()
    {
        $this->assertEquals('未知操作系统', DeviceToolkit::getOperatingSystem());

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 5.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.9 Safari/537.36';
        $this->assertEquals('Windows XP', DeviceToolkit::getOperatingSystem());

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 5.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.9 Safari/537.36';
        $this->assertEquals('Windows 2000', DeviceToolkit::getOperatingSystem());

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.119 Safari/537.36';
        $this->assertEquals('Linux', DeviceToolkit::getOperatingSystem());

        $_SERVER['HTTP_USER_AGENT'] = 'test agent';
        $this->assertEquals('未知操作系统', DeviceToolkit::getOperatingSystem());
    }
}
