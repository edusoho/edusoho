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
