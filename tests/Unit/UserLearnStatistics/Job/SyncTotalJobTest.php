<?php

namespace Tests\Unit\UserLearnStatistics\Job;

use Biz\BaseTestCase;
use Biz\UserLearnStatistics\Job\SyncTotalJob;

class SyncTotalJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->mockBiz('UserLearnStatistics:LearnStatisticsService', array(
            array('functionName' => 'getStatisticsSetting', 'returnValue' => array('currentTime' => time())),
            array('functionName' => 'batchCreateTotalStatistics', 'returnValue' => null),
        ));
        $this->mockBiz('Scheduler:SchedulerService', array(
            array('functionName' => 'disabledJob', 'returnValue' => null),
        ));
        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array('user_sync_learn_total_data_limit' => 60)),
            array('functionName' => 'set', 'returnValue' => array()),
        ));
        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'searchUsers',
                'returnValue' => array(),
            ),
        ));
        $mockedJobDao = $this->mockBiz(
            'Scheduler:JobDao',
            array(
                array(
                    'functionName' => 'update',
                ),
            )
        );

        $job = new SyncTotalJob(array(), $this->biz);
        $job->args = array('lastUserId' => 1);
        $result = $job->execute();
        $this->assertNull($result);
    }

    public function testExecute2()
    {
        $this->mockBiz('UserLearnStatistics:LearnStatisticsService', array(
            array('functionName' => 'getStatisticsSetting', 'returnValue' => array('currentTime' => time())),
            array('functionName' => 'batchCreateTotalStatistics', 'returnValue' => null),
        ));
        $this->mockBiz('Scheduler:SchedulerService', array(
            array('functionName' => 'disabledJob', 'returnValue' => null),
        ));
        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array('user_sync_learn_total_data_limit' => 60)),
            array('functionName' => 'set', 'returnValue' => array()),
        ));
        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'searchUsers',
                'returnValue' => array(array('id' => 1)),
            ),
        ));
        $mockedJobDao = $this->mockBiz(
            'Scheduler:JobDao',
            array(
                array(
                    'functionName' => 'update',
                ),
            )
        );

        $job = new SyncTotalJob(array(), $this->biz);
        $job->args = array('lastUserId' => 2);
        $job->execute();
        $mockedJobDao->shouldHaveReceived('update');
    }
}
