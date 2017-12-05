<?php

namespace Tests\Unit\UserLearnStatistics\Dao;

use Biz\BaseTestCase;

class TotalStatisticsDaoTest extends BaseTestCase
{
    public function testFindByIds()
    {
        $time = time();
        $statistic = array(
            'userId' => 1,
        );
        $this->getDailyStatisticsDao()->create($statistic);
        $statistic['userId'] = 2;
        $statistic = $this->getDailyStatisticsDao()->create($statistic);

        $this->assertEquals(2, count($this->getDailyStatisticsDao()->findByIds(array(1, 2))));
        $this->assertEquals(1, count($this->getDailyStatisticsDao()->findByIds(array(1))));
        $this->assertEquals(0, count($this->getDailyStatisticsDao()->findByIds(array())));
        $this->assertEquals(0, count($this->getDailyStatisticsDao()->findByIds(array(3, 4))));
    }

    public function testStatisticSearch()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $this->getTotalStatisticsDao()->create($defaultMockFields);

        $result = $this->getTotalStatisticsDao()->statisticSearch(
            array(
                'userIds' => array(3),
            ),
            array(
                'id' => 'DESC',
            )
        );

        $this->assertNotNull($result);
        $this->assertEquals($result[0]['userId'], $defaultMockFields['userId']);
    }

    public function testStatisticCount()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $this->getTotalStatisticsDao()->create($defaultMockFields);

        $result = $this->getTotalStatisticsDao()->statisticCount(array('userIds' => array(3)));

        $this->assertNotNull($result);
        $this->assertEquals($result, 1);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'userId' => '3',
            'joinedClassroomNum' => 1,
            'joinedCourseSetNum' => 1,
            'paidAmount' => 11,
        );
    }

    protected function getTotalStatisticsDao()
    {
        return $this->createDao('UserLearnStatistics:TotalStatisticsDao');
    }
}
