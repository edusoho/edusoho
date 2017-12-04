<?php

namespace Tests\Unit\DailyStatistics;

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

        $this->assertEquals(2, count($this->getDailyStatisticsDao()->findByIds(array(1,2))));
        $this->assertEquals(1, count($this->getDailyStatisticsDao()->findByIds(array(1))));
        $this->assertEquals(0, count($this->getDailyStatisticsDao()->findByIds(array())));
        $this->assertEquals(0, count($this->getDailyStatisticsDao()->findByIds(array(3,4))));
    }

    protected function getDailyStatisticsDao()
    {
        return $this->createDao('UserLearnStatistics:TotalStatisticsDao');
    }
}