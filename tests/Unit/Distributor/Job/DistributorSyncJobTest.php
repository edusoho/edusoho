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

        $drpService = $this->mockBiz(
            'Drp:DrpService',
            array(
                array(
                    'functionName' => 'postData',
                    'withParam' => array(
                        array('sendData1', 'sendData2'), 'User',
                    ),
                    'returnValue' => json_encode(array('code' => 'success')),
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

        $drpService->shouldHaveReceived('postData')->times(1);
        $distributorUserService->shouldHaveReceived('getDrpService')->times(1);
        $distributorUserService->shouldHaveReceived('findJobData')->times(1);
        $distributorUserService->shouldHaveReceived('getSendType')->times(2);
        $distributorUserService->shouldHaveReceived('batchUpdateStatus')->times(1);
    }

    public function testGetAvailableSendTypes()
    {
        $job = new DistributorSyncJob(array(), $this->biz);
        $result = ReflectionUtils::invokeMethod($job, 'getAvailableSendTypes');

        $this->assertArrayEquals(array('User', 'Order', 'CourseOrder'), $result);
    }
}
