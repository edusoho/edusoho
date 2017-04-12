<?php

namespace Tests\Unit\ApiBundle\Api;

use ApiBundle\Api\ResourceKernel;
use ApiBundle\ApiTestCase;
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
