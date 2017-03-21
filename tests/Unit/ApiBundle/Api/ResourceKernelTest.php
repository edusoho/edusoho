<?php

namespace Tests\Unit\ApiBundle\Api;

use ApiBundle\Api\PathParser;
use ApiBundle\Api\Resource\ResourceManager;
use ApiBundle\Api\ResourceKernel;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\HttpFoundation\Request;

class ResourceKernelTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleWithAdd()
    {
        $kernel = new ResourceKernel(
            new PathParser(),
            new ResourceManager(new Biz())
        );

        $fakeCourse = array(
            'id' => 1,
            'title' => 'fake course'
        );
        $request = Request::create('http://test.com/courses', 'POST', $fakeCourse);
        $result = $kernel->handle($request);

        $this->assertEquals($fakeCourse, $result);
    }

    public function testHandleWithUpdate()
    {
        $kernel = new ResourceKernel(
            new PathParser(),
            new ResourceManager(new Biz())
        );

        $fakeCourse = array(
            'id' => 2,
            'title' => 'fake course 2222'
        );
        $request = Request::create('http://test.com/courses/2', 'POST', $fakeCourse);
        $result = $kernel->handle($request);

        $this->assertEquals($fakeCourse, $result);
    }

    public function testHandleWithSearch()
    {
        $kernel = new ResourceKernel(
            new PathParser(),
            new ResourceManager(new Biz())
        );


        $request = Request::create('http://test.com/courses', 'GET');
        $result = $kernel->handle($request);

        $this->assertCount(2, $result);
    }

    public function testHandleWithDelete()
    {
        $kernel = new ResourceKernel(
            new PathParser(),
            new ResourceManager(new Biz())
        );


        $request = Request::create('http://test.com/courses/2', 'DELETE');
        $result = $kernel->handle($request);

        $this->assertTrue($result);
    }
}