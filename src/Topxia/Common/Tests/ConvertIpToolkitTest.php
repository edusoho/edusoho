<?php

namespace Topxia\Common\Tests;

use Topxia\Common\ConvertIpToolkit;
use Topxia\Service\Common\BaseTestCase;

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
