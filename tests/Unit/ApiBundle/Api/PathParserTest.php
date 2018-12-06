<?php

namespace Tests\Unit\ApiBundle\Api;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\PathParser;

class PathParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParseWithOneLevel()
    {
        $parser = new PathParser();
        $request = new ApiRequest('/courses/1', 'GET');
        $pathMeta = $parser->parse($request);

        $this->assertEquals('ApiBundle\Api\Resource\Course\Course', $pathMeta->getResourceClassName());
        $this->assertEquals(array(1), $pathMeta->getSlugs());
        $this->assertEquals('get', $pathMeta->getResMethod());
    }

    public function testParseWithTwoLevel()
    {
        $parser = new PathParser();
        $request = new ApiRequest('/courses/1/reviews/2', 'PATCH');
        $pathMeta = $parser->parse($request);

        $this->assertEquals('ApiBundle\Api\Resource\Course\CourseReview', $pathMeta->getResourceClassName());
        $this->assertEquals(array(1, 2), $pathMeta->getSlugs());
        $this->assertEquals('update', $pathMeta->getResMethod());
    }

    public function testParseWithThreeLevel()
    {
        $parser = new PathParser();
        $request = new ApiRequest('/courses/1/threads/2/posts/3', 'DELETE');
        $pathMeta = $parser->parse($request);

        $this->assertEquals('ApiBundle\\Api\\Resource\\Course\\CourseThreadPost', $pathMeta->getResourceClassName());
        $this->assertEquals(array(1, 2, 3), $pathMeta->getSlugs());
        $this->assertEquals('remove', $pathMeta->getResMethod());
    }

    public function testParseWithPluginResourceName()
    {
        $parser = new PathParser();
        $request = new ApiRequest('/plugins/vip/vip_levels', 'GET');
        $pathMeta = $parser->parse($request);

        $this->assertEquals('VipPlugin\\Api\\Resource\\VipLevel\\VipLevel', $pathMeta->getResourceClassName());
        $this->assertEquals(array(), $pathMeta->getSlugs());
        $this->assertEquals('search', $pathMeta->getResMethod());
    }

    public function testParseWithPluginResourceNameTwoLevel()
    {
        $parser = new PathParser();
        $request = new ApiRequest('/plugins/vip/vip_levels/1/members', 'GET');
        $pathMeta = $parser->parse($request);

        $this->assertEquals('VipPlugin\\Api\\Resource\\VipLevel\\VipLevelMember', $pathMeta->getResourceClassName());
        $this->assertEquals(array(1), $pathMeta->getSlugs());
        $this->assertEquals('search', $pathMeta->getResMethod());
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testParseWithException()
    {
        $parser = new PathParser();
        $request = new ApiRequest('/', 'GET');
        $pathMeta = $parser->parse($request);
        $pathMeta->getResourceClassName();
    }
}
