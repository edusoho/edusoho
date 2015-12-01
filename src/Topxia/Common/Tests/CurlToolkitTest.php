<?php

namespace Topxia\Common\Tests;

use Topxia\Common\CurlToolkit;
use Topxia\Service\Common\BaseTestCase;

class CurlTookitTest extends BaseTestCase
{
    public function testRequestWithPostMethod()
    {
        $method = 'POST';
        $url    = "http://www.baidu.com";
        $result = CurlToolkit::request('POST', "http://www.edusoho.com/question/get/token", array());
        var_dump($result);

    }

    public function testRequestWithGetMethod()
    {
        $url  = "https://api.douban.com/v2/book/17604305";
        $data = array(
            'fields' => 'id,title,url'
        );
        $result = CurlToolkit::request('GET', $url, $data);
        var_dump($result);
    }
}
