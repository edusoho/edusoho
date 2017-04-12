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

        $this->assertNotNull($result);
    }

    /**
     * @expectedException \ApiBundle\Api\Exception\InvalidArgumentException
     */
    public function testBatchRequestWithNoBatchParam()
    {
        $kernel = new ResourceKernel(
            $this->getContainer()
        );

        $request = Request::create('http://test.com/batch', 'POST');
        $kernel->handle($request);
    }

    /**
     * @expectedException \ApiBundle\Api\Exception\InvalidArgumentException
     */
    public function testBatchRequestWithStringBatch()
    {
        $kernel = new ResourceKernel(
            $this->getContainer()
        );

        $request = Request::create('http://test.com/batch', 'POST', array(
            'batch' => 'aabbcc',
        ));
        $kernel->handle($request);
    }

    /**
     * @expectedException \ApiBundle\Api\Exception\InvalidArgumentException
     */
    public function testBatchRequestWithWrongParams()
    {
        $kernel = new ResourceKernel(
            $this->getContainer()
        );

        $batchParam = array(
            array('method' => 'GET'),
        );
        $request = Request::create('http://test.com/batch', 'POST', array(
            'batch' => json_encode($batchParam),
        ));
        $kernel->handle($request);
    }

    public function testBatchRequestWithTwoGet()
    {
        $kernel = new ResourceKernel(
            $this->getContainer()
        );

        $batchParam = array(
            array('method' => 'GET', 'relative_url' => '/course_sets'),
            array('method' => 'GET', 'relative_url' => '/courses'),
        );
        $request = Request::create('http://test.com/batch', 'POST', array(
            'batch' => json_encode($batchParam),
        ));
        $result = $kernel->handle($request);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('body', $result[0]);
        $this->assertArrayHasKey('code', $result[0]);
        $this->assertEquals(200, $result[0]['code']);
    }

    public function testBatchRequestWithMix()
    {
        $kernel = new ResourceKernel(
            $this->getContainer()
        );

        $batchParam = array(
            array('method' => 'GET', 'relative_url' => '/course_sets'),
            array('method' => 'POST', 'relative_url' => '/tokens', 'body' => 'username=admin&password=6fPHfubFUWCgaNjN'),
        );
        $request = Request::create('http://test.com/batch', 'POST', array(
            'batch' => json_encode($batchParam),
        ));
        $result = $kernel->handle($request);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('body', $result[0]);
        $this->assertArrayHasKey('code', $result[0]);
        $this->assertEquals(200, $result[0]['code']);
        $this->assertArrayHasKey('token', $result[1]['body']);
        $this->assertArrayHasKey('user', $result[1]['body']);
        $this->assertEquals('admin', $result[1]['body']['user']['nickname']);
    }
}
