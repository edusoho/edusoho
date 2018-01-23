<?php

namespace Tests\Unit\Distributor\Job;

use Biz\BaseTestCase;
use Biz\Distributor\Job\DistributorSyncJob;
use AppBundle\Common\ReflectionUtils;

class DistributorSyncJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $sendedData = array(
            array(
                'id' => 1,
                'data' => 'sendData1',
            ),
            array(
                'id' => 2,
                'data' => 'sendData2',
            ),
        );

        $response = $this->mockBiz(
            'Response:Response',
            array(
                array(
                    'functionName' => 'getBody',
                    'withParam' => array(),
                    'returnValue' => json_encode(array('code' => 'success')),
                ),
            )
        );

        $drpService = $this->mockBiz(
            'Drp:DrpService',
            array(
                array(
                    'functionName' => 'postData',
                    'withParam' => array(
                        array('sendData1', 'sendData2'), 'User',
                    ),
                    'returnValue' => $response,
                ),
            )
        );
        $distributorUserService = $this->mockBiz(
            'Distributor:DistributorUserService',
            array(
                array(
                    'functionName' => 'getDrpService',
                    'withParam' => array(),
                    'returnValue' => $drpService,
                ),
                array(
                    'functionName' => 'findJobData',
                    'withParam' => array(),
                    'returnValue' => $sendedData,
                ),
                array(
                    'functionName' => 'getSendType',
                    'withParam' => array(),
                    'returnValue' => 'User',
                ),
                array(
                    'functionName' => 'batchUpdateStatus',
                    'withParam' => array($sendedData, 'finished'),
                ),
            )
        );

        $job = new DistributorSyncJob(array(), $this->biz);
        ReflectionUtils::setProperty($job, 'mockedSendTypes', array('User'));
        $result = $job->execute();

        $this->assertNull($result);

        $response->shouldHaveReceived('getBody')->times(2);
        $drpService->shouldHaveReceived('postData')->times(1);
        $distributorUserService->shouldHaveReceived('getDrpService')->times(1);
        $distributorUserService->shouldHaveReceived('findJobData')->times(1);
        $distributorUserService->shouldHaveReceived('getSendType')->times(1);
        $distributorUserService->shouldHaveReceived('batchUpdateStatus')->times(1);
    }
}
