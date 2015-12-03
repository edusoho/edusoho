<?php

namespace Topxia\Common\Tests;

use Topxia\Common\CurlToolkit;
use Topxia\Service\Common\BaseTestCase;

class CurlTookitTest extends BaseTestCase
{
    public function testRequestWithPostMethod()
    {
        $url  = "http://v.juhe.cn/postcode/query";
        $data = array(
            'postcode' => '310018',
            'key'      => '1c85799c8c1a6675fb8c312e4b7a09d3'

        );
        $result = CurlToolkit::request('POST', $url, $data);
        $this->assertEquals(0, $result['error_code']);

    }

    public function testRequestWithGetMethod()
    {
        $url  = "http://v.juhe.cn/postcode/query";
        $data = array(
            'postcode' => '310018',
            'key'      => '1c85799c8c1a6675fb8c312e4b7a09d3'

        );
        $result = CurlToolkit::request('GET', $url, $data);
        $this->assertEquals(0, $result['error_code']);
    }
}
