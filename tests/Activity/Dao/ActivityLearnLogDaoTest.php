<?php

namespace Tests\Activity\Dao;

use Tests\Base\BaseDaoTestCase;

class ActivityLearnLogDaoTest extends BaseDaoTestCase
{
    public function testSumLearnedTimeByActivityIdAndUserId()
    {
        $factor0 = $this->mockDataObject(array('activityId' => 0, 'userId' => 1));
        $factor1 = $this->mockDataObject(array('activityId' => 0, 'userId' => 1));
        $factor2 = $this->mockDataObject(array('activityId' => 0, 'userId' => 1));

        $res = $this->getDao()->sumLearnedTimeByActivityIdAndUserId(0, 1);

        $this->assertEquals($this->getSums(array($factor0, $factor1, $factor2)), $res);
    }

    // Todo 连表查询
    public function testSumLearnedTimeByCourseIdAndUserId()
    {
        ;
    }

    public function testFindByActivityIdAndUserIdAndEvent()
    {
        $log[0] = $this->mockDataObject(array('activityId' => 0, 'userId' => 1));
        $log[1] = $this->mockDataObject(array('activityId' => 0, 'userId' => 1));
        $log[2] = $this->mockDataObject(array('activityId' => 0, 'userId' => 1));

        $res = $this->getDao()->findByActivityIdAndUserIdAndEvent(0, 1, 'ffff');

        foreach ($log as $key => $val) {
            $this->assertArrayEquals($log[$key], $res[$key], $this->getCompareKeys());
        }
    }

    // Todo 连表查询
    public function testCountLearnedDaysByCourseIdAndUserId()
    {
        ;
    }

    // Todo 连表查询
    public function testSumLearnTime()
    {
        ;
    }
    
    protected function fetchAndAssembleIds(array $rawInput)
    {
        $res = array();
        foreach ($rawInput as $val) {
            $res[] = $val['id'];
        }

        return $res;
    }

    protected function getSums(array $rawInput)
    {
        $sum = 0;
        foreach ($rawInput as $val) {
            if (is_array($val)) {
                if (isset($val['learnedTime'])) {
                    $sum += $val['learnedTime'];
                } else {
                    var_dump($val);
                    throw new \Exception('?');
                }
            } elseif (is_numeric($val)) {
                $sum += $val;
            } else {
                throw new \Exception($val);
            }
        }

        return $sum;
    }

    protected function getDefaultMockFields($learnedTime)
    {
        if (!$learnedTime) {
            $learnedTime = rand(0, 1000);
        }
        return array(
            'activityId' => rand(0, 1000),
            'userId' => rand(0, 1000),
            'event' => 'ffff',
            'data' => '啊',
            'learnedTime' => $learnedTime,
            'courseTaskId' => rand(0, 1000)
        );
    }
}
