<?php

namespace Tests\Unit\AppBundle\Common\Tests;

use AppBundle\Common\ConvertIpToolkit;
use Biz\BaseTestCase;

class ConvertIpToolkitTest extends BaseTestCase
{
    public function testConvertIp()
    {
        $result = ConvertIpToolkit::convertIp('8.8.8.8');

        $this->assertEquals('GOOGLE.COM', $result);

        $result = ConvertIpToolkit::convertIp('223.5.5.5');
        $this->assertEquals('ALIDNS.COM', $result);

        $result = ConvertIpToolkit::convertIp('127.0.0.1');
        $this->assertEquals('本机地址', $result);
    }
}
