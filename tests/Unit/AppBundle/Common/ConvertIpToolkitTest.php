<?php

namespace Tests\Unit\AppBundle\Common\Tests;

use AppBundle\Common\ConvertIpToolkit;
use Biz\BaseTestCase;

class ConvertIpToolkitTest extends BaseTestCase
{
    public function testConvertIp()
    {
        $result = ConvertIpToolkit::convertIp('8.8.8.8');
        $this->assertEquals('美国', $result);

        $result = ConvertIpToolkit::convertIp('223.5.5.5');
        $this->assertEquals('中国', $result);
    }
}
