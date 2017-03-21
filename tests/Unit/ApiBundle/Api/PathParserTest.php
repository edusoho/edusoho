<?php

namespace Tests\Unit\ApiBundle\Api;

use ApiBundle\Api\PathParser;
use Symfony\Component\HttpFoundation\Request;

class PathParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParseWithOneLevel()
    {
        $parser = new PathParser();
        $request = Request::create('http://test.com/courses/1', 'GET');
        $pathMeta = $parser->parse($request);

        $this->assertEquals('Course\\Course', $pathMeta->getQualifiedResName());
        $this->assertEquals(array('courseId' => 1), $pathMeta->getSlugs());
        $this->assertEquals('get', $pathMeta->getResMethod());
    }

    public function testParseWithTwoLevel()
    {
        $parser = new PathParser();
        $request = Request::create('http://test.com/courses/1/users/2', 'POST');
        $pathMeta = $parser->parse($request);

        $this->assertEquals('Course\\CourseUser', $pathMeta->getQualifiedResName());
        $this->assertEquals(array('courseId' => 1, 'userId' => 2), $pathMeta->getSlugs());
        $this->assertEquals('update', $pathMeta->getResMethod());
    }

    public function testParseWithThreeLevel()
    {
        $parser = new PathParser();
        $request = Request::create('http://test.com/courses/1/threads/2/posts/3', 'DELETE');
        $pathMeta = $parser->parse($request);

        $this->assertEquals('Course\\CourseThreadPost', $pathMeta->getQualifiedResName());
        $this->assertEquals(array('courseId' => 1, 'threadId' => 2, 'postId' => 3), $pathMeta->getSlugs());
        $this->assertEquals('remove', $pathMeta->getResMethod());
    }


    /**
     * @expectedException ApiBundle\Api\Exception\BadRequestException
     */
    public function testParseWithException()
    {
        $parser = new PathParser();
        $request = Request::create('http://test.com');
        $pathMeta = $parser->parse($request);
        $pathMeta->getQualifiedResName();
    }
}