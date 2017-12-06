<?php

namespace Tests\Unit\UserLearnStatistics;

use Biz\BaseTestCase;

class LearnStatisticsServiceTest extends BaseTestCase
{
    public function testStatisticsDataSearch()
    {
        $conditions = array(
            'startDate' => '2017-11-08',
            'endDate' => '',
            'nickname' => '',
            'isDefault' => 'false',
            'userIds' => array(3),
        );
        $order = array(
            'id' => 'DESC',
        );
        $preResult = array(
            array(
                'userId' => 3,
                'joinedClassroomNum' => 2,
                'joinedCourseSetNum' => 2,
            ),
        );

        $this->mockBiz('UserLearnStatistics:DailyStatisticsDao');
        $this->getDailyStatisticsDao()->shouldReceive('statisticSearch')->andReturn($preResult);
        $result = $this->getLearnStatisticsService()->statisticsDataSearch($conditions, $order);

        $this->assertEquals($result[0]['userId'], $preResult[0]['userId']);
        $this->assertEquals($result[0]['joinedClassroomNum'], $preResult[0]['joinedClassroomNum']);
    }

    public function testStatisticsDataCount()
    {
        $conditions = array(
            'startDate' => '',
            'endDate' => '',
            'nickname' => '',
            'isDefault' => 'false',
            'userIds' => array(3),
        );

        $this->mockBiz('UserLearnStatistics:TotalStatisticsDao');
        $this->getTotalStatisticsDao()->shouldReceive('statisticCount')->andReturn(1);

        $result = $this->getLearnStatisticsService()->statisticsDataCount($conditions);
        $this->assertEquals($result, 1);
    }

    public function testSearchTotalStatistics()
    {
        $this->getTotalStatisticsDao()->create(array('userId' => 1));
        $this->getTotalStatisticsDao()->create(array('userId' => 2));

        $result = $this->getLearnStatisticsService()->searchTotalStatistics(array(), array(), 0, 2);
        $this->assertEquals(2, count($result));

        $result = $this->getLearnStatisticsService()->searchTotalStatistics(array('userId' => 1), array(), 0, 2);
        $this->assertEquals(1, count($result));

        $result = $this->getLearnStatisticsService()->searchTotalStatistics(array(), array(), 0, 1);
        $this->assertEquals(1, count($result));
    }

    public function testCountTotalStatistics()
    {
        $this->getTotalStatisticsDao()->create(array('userId' => 1));
        $this->getTotalStatisticsDao()->create(array('userId' => 2));

        $count = $this->getLearnStatisticsService()->countTotalStatistics(array());
        $this->assertEquals(2, $count);

        $count = $this->getLearnStatisticsService()->countTotalStatistics(array('userId' => 1));
        $this->assertEquals(1, $count);
    }

    public function testSearchDailyStatistics()
    {
        $this->getDailyStatisticsDao()->create(array('userId' => 1));
        $this->getDailyStatisticsDao()->create(array('userId' => 2));
        $result = $this->getLearnStatisticsService()->searchDailyStatistics(array(), array(), 0, 2);
        $this->assertEquals(2, count($result));

        $result = $this->getLearnStatisticsService()->searchDailyStatistics(array('userId' => 1), array(), 0, 2);
        $this->assertEquals(1, count($result));

        $result = $this->getLearnStatisticsService()->searchDailyStatistics(array(), array(), 0, 1);
        $this->assertEquals(1, count($result));
    }

    public function testCountDailyStatistics()
    {
        $this->getDailyStatisticsDao()->create(array('userId' => 1));
        $this->getDailyStatisticsDao()->create(array('userId' => 2));
        $count = $this->getLearnStatisticsService()->countDailyStatistics(array());

        $this->assertEquals(2, $count);
    }

    public function batchDeletePastDailyStatistics()
    {
        $this->getDailyStatisticsDao()->create(array('userId' => 1, 'recordTime' => 2));
        $this->getDailyStatisticsDao()->create(array('userId' => 1, 'recordTime' => 3));
        $this->getDailyStatisticsDao()->create(array('userId' => 1, 'recordTime' => 10));
        $this->getDailyStatisticsDao()->create(array('userId' => 2, 'recordTime' => 3));

        $this->getLearnStatisticsService()->batchDeletePastDailyStatistics(array('recordTime_GE' => 10));
        $this->assertEquals(3, $this->getDailyStatisticsDao()->count(array()));
        $this->getLearnStatisticsService()->batchDeletePastDailyStatistics(array('userIds' => array('1')));
        $this->assertEquals(1, $this->getDailyStatisticsDao()->count(array()));
    }

    public function getStatisticsSetting()
    {
        $setting = $this->getLearnStatisticsService()->getStatisticsSetting();
        $this->assertNotEmpty($setting);
        $this->assertNotEmpty($setting['timespan']);
        $this->assertNotEmpty($setting['currentTime']);
    }

    protected function getLearnStatisticsService()
    {
        return $this->createService('UserLearnStatistics:LearnStatisticsService');
    }

    protected function getTotalStatisticsDao()
    {
        return $this->createDao('UserLearnStatistics:TotalStatisticsDao');
    }

    protected function getDailyStatisticsDao()
    {
        return $this->createDao('UserLearnStatistics:DailyStatisticsDao');
    }
}
