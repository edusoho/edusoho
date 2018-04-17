<?php

namespace Tests\Unit\CloudPlatform\QueueJob;

use Biz\BaseTestCase;
use Biz\CloudPlatform\QueueJob\SearchJob;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SearchJobTest extends BaseTestCase
{
    public function testExecuteUpdate()
    {
        $context = array(
            'type' => 'update',
            'args' => array(),
        );

        $this->mockBiz('CloudPlatform:SearchService', array(
            array(
                'functionName' => 'notifyUpdate',
                'withParams' => array(),
                'returnValue' => array(
                    'success' => true,
                ),
            ),
        ));

        $job = new SearchJob($context);
        $job->setBiz($this->biz);
        $result = $job->execute();
        $this->assertNull($result);
    }

    public function testExecuteDelete()
    {
        $context = array(
            'type' => 'delete',
            'args' => array(),
        );

        $this->mockBiz('CloudPlatform:SearchService', array(
            array(
                'functionName' => 'notifyDelete',
                'withParams' => array(),
                'returnValue' => array(
                    'success' => true,
                ),
            ),
        ));

        $job = new SearchJob($context);
        $job->setBiz($this->biz);
        $result = $job->execute();
        $this->assertNull($result);
    }

    public function testExecuteWithWrongType()
    {
        $context = array(
            'type' => 'wrong',
            'args' => array(),
        );

        $job = new SearchJob($context);
        $job->setBiz($this->biz);
        $result = $job->execute();
        $this->assertEquals(array(1, '只支持delete,update两种类型，你的类型是wrong'), $result);
    }

    public function testExecuteWithNotifyError()
    {
        $context = array(
            'type' => 'delete',
            'args' => array(),
        );

        $this->mockBiz('CloudPlatform:SearchService', array(
            array(
                'functionName' => 'notifyDelete',
                'withParams' => array(),
                'returnValue' => array(
                    'error' => true,
                ),
            ),
        ));

        $job = new SearchJob($context);
        $job->setBiz($this->biz);
        $result = $job->execute();
        $this->assertEquals(array(1, true), $result);
    }

    public function testWithNotifyException()
    {
        $context = array(
            'type' => 'delete',
            'args' => array(),
        );

        $this->mockBiz('CloudPlatform:SearchService', array(
            array(
                'functionName' => 'notifyDelete',
                'withParams' => array(),
                'throwException' => new NotFoundHttpException('error'),
            ),
        ));

        $job = new SearchJob($context);
        $job->setBiz($this->biz);
        $result = $job->execute();
        $this->assertEquals(array(1, 'error'), $result);
    }
}
