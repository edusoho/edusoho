<?php

namespace Tests\Unit\UserLearnStatistics\Dao;

use Biz\BaseTestCase;
use Biz\UserLearnStatistics\Dao\DailyStatisticsDao;

class DailyStatisticsDaoTest extends BaseTestCase
{
    public function testStatisticSearch()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $this->getDailyStatisticsDao()->create($defaultMockFields);
        $this->getDailyStatisticsDao()->create(
            array(
                'userId' => '3',
                'joinedClassroomNum' => 1,
                'joinedCourseSetNum' => 1,
                'paidAmount' => 11,
                'recordTime' => 223334,
            )
        );

        $result = $this->getDailyStatisticsDao()->statisticSearch(
            array(
                'createTime_LE' => time(),
                'userIds' => array(3),
            ),
            array('id' => 'DESC')
        );

        $this->assertNotNull($result);
        $this->assertEquals($result[0]['userId'], $defaultMockFields['userId']);
        $this->assertEquals($result[0]['joinedClassroomNum'], 2);
    }

    public function testStatisticCount()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $this->getDailyStatisticsDao()->create($defaultMockFields);

        $result = $this->getDailyStatisticsDao()->statisticCount(array('createTime_LE' => time(), 'userIds' => array(3)));

        $this->assertNotNull($result);
        $this->assertEquals($result, 1);
    }

    public function testFindUserDailyLearnTimeByDate()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $this->getDailyStatisticsDao()->create($defaultMockFields);

        $result = $this->getDailyStatisticsDao()->findUserDailyLearnTimeByDate(array('userId' => 3, 'recordTime_GE' => time() - 24 * 3600));
        $this->assertEquals(1, count($result));
        $statistics = reset($result);
        $this->assertEquals(10, $statistics['learnedTime']);
    }

    public function testUpdateStorageByIds()
    {
        $time = time();
        $statistic = array(
            'id' => 1,
            'userId' => 1,
            'recordTime' => $time,
            'isStorage' => 0,
        );
        $statistic = $this->getDailyStatisticsDao()->create($statistic);

        $this->getDailyStatisticsDao()->updateStorageByIds(array(2));
        $statistic = $this->getDailyStatisticsDao()->get(1);
        $this->assertEquals(0, $statistic['isStorage']);

        $this->getDailyStatisticsDao()->updateStorageByIds(array(1));
        $statistic = $this->getDailyStatisticsDao()->get(1);
        $this->assertEquals(1, $statistic['isStorage']);
    }

    public function testFindByIds()
    {
        $time = time();
        $statistic = array(
            'userId' => 1,
            'recordTime' => $time,
        );
        $this->getDailyStatisticsDao()->create($statistic);
        $statistic['userId'] = 2;
        $statistic = $this->getDailyStatisticsDao()->create($statistic);

        $this->assertEquals(2, count($this->getDailyStatisticsDao()->findByIds(array(1, 2))));
        $this->assertEquals(1, count($this->getDailyStatisticsDao()->findByIds(array(1))));
        $this->assertEquals(0, count($this->getDailyStatisticsDao()->findByIds(array())));
        $this->assertEquals(0, count($this->getDailyStatisticsDao()->findByIds(array(3, 4))));
    }

    protected function getDefaultMockFields()
    {
        return array(
            'userId' => '3',
            'joinedClassroomNum' => 1,
            'joinedCourseSetNum' => 1,
            'paidAmount' => 11,
            'learnedSeconds' => 10,
            'recordTime' => time(),
        );
    }

    /**
     * @return DailyStatisticsDao
     */
    protected function getDailyStatisticsDao()
    {
        return $this->createDao('UserLearnStatistics:DailyStatisticsDao');
    }
}
