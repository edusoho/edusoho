<?php

namespace Tests\Activity\Dao;

use Tests\Base\BaseDaoTestCase;

class ActivityLearnLogDaoTest extends BaseDaoTestCase
{
    public function testSumLearnedTimeByActivityIdAndUserId()
    {
        $factor0 = $this->mockActivityLearnLog(array('activityId' => 0, 'userId' => 1));
        $factor1 = $this->mockActivityLearnLog(array('activityId' => 0, 'userId' => 1));
        $factor2 = $this->mockActivityLearnLog(array('activityId' => 0, 'userId' => 1));

        $res = $this->getActivityLearnLogDao()->sumLearnedTimeByActivityIdAndUserId(0, 1);

        $this->assertEquals($this->getSums(array($factor0, $factor1, $factor2)), $res);
    }

    public function testSumLearnedTimeByCourseIdAndUserId()
    {
        $factor0 = $this->mockActivityLearnLog(array('courseId' => 0, 'userId' => 1));
        $factor1 = $this->mockActivityLearnLog(array('courseId' => 0, 'userId' => 1));
        $factor2 = $this->mockActivityLearnLog(array('courseId' => 0, 'userId' => 1));

        $res = $this->getActivityLearnLogDao()->sumLearnedTimeByCourseIdAndUserId(0, 1);

        $this->assertEquals($this->getSums(array($factor0, $factor1, $factor2)), $res);
    }

    public function testFindActivityLearnLogsByActivityIdAndUserIdAndEvent()
    {
        $log[0] = $this->mockActivityLearnLog(array('activityId' => 0, 'userId' => 1));
        $log[1] = $this->mockActivityLearnLog(array('activityId' => 0, 'userId' => 1));
        $log[2] = $this->mockActivityLearnLog(array('activityId' => 0, 'userId' => 1));

        $res = $this->getActivityLearnLogDao()->findActivityLearnLogsByActivityIdAndUserIdAndEvent(0, 1, 'ffff');

        foreach ($log as $key => $val) {
            $this->assertArrayEquals($log[$key], $res[$key], $this->getCompareKeys());
        }
    }

    private function getCompareKeys()
    {
        $default = $this->getDefaultMockFields(null);
        return array_keys($default);
    }

    private function fetchAndAssembleIds(array $rawInput)
    {
        $res = array();
        foreach ($rawInput as $val) {
            $res[] = $val['id'];
        }

        return $res;
    }

    private function getSums(array $rawInput)
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

    private function mockActivityLearnLog($fields = array(), $learnedTime = null)
    {
        return $this->getActivityLearnLogDao()->create(array_merge($this->getDefaultMockFields($learnedTime), $fields));
    }

    private function getDefaultMockFields($learnedTime)
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

    private function getActivityLearnLogDao()
    {
        return $this->getBiz()->dao('Activity:ActivityLearnLogDao');
    }
}
