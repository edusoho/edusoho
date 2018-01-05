<?php

namespace Tests\Unit\AppBundle\Common\Tests;

use AppBundle\Common\CurlToolkit;
use Biz\BaseTestCase;

class CurlToolkitTest extends BaseTestCase
{
    /**
     * @expectedException \AppBundle\Common\Exception\AccessDeniedException
     */
    public function testHostNotInWhite()
    {
        CurlToolkit::request('get', 'http://www.baidu.com');
    }

    public function testCurl()
    {
        $result = CurlToolkit::request('get', 'http://open.edusoho.com/api/v1/context/notice');
        $this->assertArrayHasKey('content', $result[0]);
        $this->assertArrayHasKey('publishedTime', $result[0]);
        $this->assertArrayHasKey('detailUrl', $result[0]);

        $result = CurlToolkit::request('get', 'http://open.edusoho.com/api/v1/context/notice?kw=edusoho', array(), array('contentType' => 'plain'));
        $this->assertTrue(is_array(json_decode($result, true)));

        $result = CurlToolkit::request('POST', 'http://www.edusoho.com/question/get/token');
        if (empty($result)) {
            $this->assertEmpty($result);
        } else {
            $this->assertEquals(32, strlen($result));
        }

        $result = CurlToolkit::request('PUT', 'http://www.edusoho.com/question/get/token');
        $this->assertEquals(32, strlen($result));
        if (empty($result)) {
            $this->assertEmpty($result);
        } else {
            $this->assertEquals(32, strlen($result));
        }

        $result = CurlToolkit::request('PATCH', 'http://www.edusoho.com/question/get/token');
        $this->assertEquals(32, strlen($result));if (empty($result)) {
            $this->assertEmpty($result);
        } else {
            $this->assertEquals(32, strlen($result));
        }
        if (empty($result)) {
            $this->assertEmpty($result);
        } else {
            $this->assertEquals(32, strlen($result));
        }

        $result = CurlToolkit::request('DELETE', 'http://www.edusoho.com/question/get/token');
        $this->assertEquals(32, strlen($result));
        if (empty($result)) {
            $this->assertEmpty($result);
        } else {
            $this->assertEquals(32, strlen($result));
        }
    }
}
