<?php

namespace Tests\Unit\Xapi\Job;

use Biz\BaseTestCase;
use Biz\Xapi\Job\PushStatementJob;
use Biz\Xapi\Service\XapiService;

class PushStatementJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array('enabled' => 1)),
        ));

        $xapiSdk = $this->getXapiService()->getXapiSdk();
        $mockObject = \Mockery::mock($xapiSdk);
        $mockObject->shouldReceive('pushStatements')->times(1)->andReturn(array(
            '1234567', '4354765',
        ));

        $this->mockBiz('Xapi:XapiService', array(
            array(
                'functionName' => 'searchStatements',
                'returnValue' => array(
                    array(
                        'id' => '1',
                        'uuid' => '1234567890',
                        'data' => array('uuid' => 'test'),
                    ),
                ),
            ),
            array(
                'functionName' => 'getXapiSdk',
                'returnValue' => $mockObject,
            ),
            array(
                'functionName' => 'updateStatementsPushingByStatementIds',
                'returnValue' => array(),
            ),
            array(
                'functionName' => 'updateStatusPushedAndPushedTimeByUuids',
                'returnValue' => array(),
            ),
        ));

        $job = new PushStatementJob(array(), $this->getBiz());

        $this->assertNull($job->execute());
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->createService('Xapi:XapiService');
    }
}
