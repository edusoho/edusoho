<?php

namespace Tests\Unit\DailyStatistics;

use Biz\BaseTestCase;

class DailyStatisticsDaoTest extends BaseTestCase
{
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

    protected function getDailyStatisticsDao()
    {
        return $this->createDao('UserLearnStatistics:DailyStatisticsDao');
    }
}
