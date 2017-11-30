<?php

namespace Tests\Unit\UserLearnStatistics\Dao;

use Biz\BaseTestCase;

class DailyStatisticsDaoTest extends BaseTestCase
{
    public function testStatisticSearch()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $this->getDailyStatisticsDao()->create($defaultMockFields);

        $result = $this->getDailyStatisticsDao()->statisticSearch(
            array(
                'createTime_LE' => time(),
                'userIds' => array(3)
            ),
            array('id' => 'DESC')
        );

        $this->assertNotNull($result);
        $this->assertEquals($result[0]['userId'], $defaultMockFields['userId']);
    }

    public function testStatisticCount()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $this->getDailyStatisticsDao()->create($defaultMockFields);

        $result = $this->getDailyStatisticsDao()->statisticCount(array('createTime_LE' => time(), 'userIds' => array(3)));

        $this->assertNotNull($result);
        $this->assertEquals($result, 1);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'userId' => '3',
            'joinedClassroomNum' => 1,
            'joinedClassroomCourseSetNum' => 1,
            'joinedClassroomCourseNum' => 1,
            'joinedCourseSetNum' => 1,
            'paidAmount' => 11,
            'recordTime' => time()
        );
    }

    protected function getDailyStatisticsDao()
    {
        return $this->createDao('UserLearnStatistics:DailyStatisticsDao');
    }
}
