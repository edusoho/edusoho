<?php

namespace Tests\Activity\Dao;

use Tests\Base\BaseDaoTestCase;

class ActivityLearnLogDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('activityId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $factor,
                'expectedCount' => 3
            ),
            array(
                'condition' => array('userId' => 1),
                'expectedResults' => array($factor[0], $factor[1]),
                'expectedCount' => 2
            ),
            array(
                'condition' => array('activityId' => 1),
                'expectedResults' => array($factor[0], $factor[2]),
                'expectedCount' => 2
            ),
            array(
                'condition' => array('userId' => 1, 'activityId' => 1),
                'expectedResults' => array($factor[0]),
                'expectedCount' => 1
            ),
            array(
                'condition' => array('userId' => 2, 'activityId' => 2),
                'expectedResults' => array(),
                'expectedCount' => 0
            ),
        );
        
        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testSumLearnedTimeByActivityIdAndUserId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject();

        $res = $this->getDao()->sumLearnedTimeByActivityIdAndUserId(1, 1);

        $this->assertEquals($this->getSums($factor), $res);
    }

    // Todo 连表查询
    public function testSumLearnedTimeByCourseIdAndUserId()
    {
        ;
    }

    public function testFindByActivityIdAndUserIdAndEvent()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject();

        $res = $this->getDao()->findByActivityIdAndUserIdAndEvent(1, 1, 'ffff');

        foreach ($factor as $key => $val) {
            $this->assertArrayEquals($val, $res[$key], $this->getCompareKeys());
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

    protected function getDefaultMockFields()
    {
        return array(
            'activityId' => 1,
            'userId' => 1,
            'event' => 'ffff',
            'data' => '啊',
            'learnedTime' => 1,
            'courseTaskId' => 1
        );
    }
}
