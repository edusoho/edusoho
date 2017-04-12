<?php

namespace Tests\Unit\ApiBundle\Api;

use ApiBundle\Api\PathParser;
use ApiBundle\Api\Resource\CourseSet\CourseSet;
use ApiBundle\Api\Resource\Resource;
use ApiBundle\Api\Resource\ResourceManager;
use ApiBundle\Api\ResourceKernel;
use ApiBundle\ApiTestCase;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Tests\ContainerTest;
use Symfony\Component\HttpFoundation\Request;

class ResourceKernelTest extends ApiTestCase
{
    public function testHandle()
    {
        $kernel = new ResourceKernel(
            $this->getContainer()
        );

        $request = Request::create('http://test.com/course_sets', 'GET');
        $result = $kernel->handle($request);

        $this->assertNull($result);
    }
}