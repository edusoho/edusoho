<?php

namespace Tests\Unit\AppBundle\Common\Tests;

use AppBundle\Common\ConvertIpToolkit;
use Biz\BaseTestCase;

class ConvertIpToolkitTest extends BaseTestCase
{
    public function testConcertIps()
    {
        $ips = array(
            array('ip' => 'test'),
            array('ip' => '8.8.8.8'),
            array('ip' => '223.5.5.5'),
            array('ip' => '127.0.0.1'),
        );

        $expected = array(
            array('ip' => 'test', 'location' => '未知区域'),
            array('ip' => '8.8.8.8', 'location' => 'GOOGLE.COM'),
            array('ip' => '223.5.5.5', 'location' => 'ALIDNS.COM'),
            array('ip' => '127.0.0.1', 'location' => '本机地址'),
        );

        $result = ConvertIpToolkit::convertIps($ips);
        $this->assertEquals($expected, $result);
    }

    public function testConvertIp()
    {
        $result = ConvertIpToolkit::convertIp('8.8.8.8');

        $this->assertEquals('GOOGLE.COM', $result);

        $result = ConvertIpToolkit::convertIp('223.5.5.5');
        $this->assertEquals('ALIDNS.COM', $result);

        $result = ConvertIpToolkit::convertIp('127.0.0.1');
        $this->assertEquals('本机地址', $result);

        $result = ConvertIpToolkit::convertIp('test');
        $this->assertEquals('N/A', $result);
    }
}
