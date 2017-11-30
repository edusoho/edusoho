<?php

namespace Tests\Unit\UserLearnStatistics\Dao;

use Biz\BaseTestCase;

class TotalStatisticsDaoTest extends BaseTestCase
{
    public function testStatisticSearch()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $this->getTotalStatisticsDao()->create($defaultMockFields);

        $result = $this->getTotalStatisticsDao()->statisticSearch(
            array(
                'userIds' => array(3)
            ),
            array(
                'id' => 'DESC'
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
            'joinedClassroomCourseSetNum' => 1,
            'joinedClassroomCourseNum' => 1,
            'joinedCourseSetNum' => 1,
            'paidAmount' => 11
        );
    }

    protected function getTotalStatisticsDao()
    {
        return $this->createDao('UserLearnStatistics:TotalStatisticsDao');
    }
}
