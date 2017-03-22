<?php

namespace Tests\Unit\ApiBundle\Api;

use ApiBundle\Api\PathParser;
use ApiBundle\Api\Resource\CourseSet\CourseSet;
use ApiBundle\Api\Resource\Resource;
use ApiBundle\Api\Resource\ResourceManager;
use ApiBundle\Api\ResourceKernel;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\HttpFoundation\Request;

class ResourceKernelTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $fakeResource = array(
            'id' => 1,
            'title' => 'fake course'
        );
        $resourceStub = $this->mockResource($fakeResource);
        $resManagerStub = $this->mockResManager($resourceStub);

        $kernel = new ResourceKernel(
            new PathParser(),
            $resManagerStub
        );

        $request = Request::create('http://test.com/resources', 'POST', $fakeResource);
        $result = $kernel->handle($request);

        $this->assertEquals($fakeResource, $result);
    }

    private function mockResource($fakeResource)
    {
        $stub = $this->createMock(CourseSet::class);

        // 配置桩件。
        $stub->method('add')
            ->willReturn($fakeResource);
        return $stub;
    }

    private function mockResManager($resourceStub)
    {
        $stub = $this->createMock(ResourceManager::class);

        // 配置桩件。
        $stub->method('create')
            ->willReturn($resourceStub);

        return $stub;
    }
}