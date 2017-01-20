<?php

namespace Tests\Course\Dao;

use Tests\Base\BaseDaoTestCase;

class ThreadPostDaoTest extends BaseDaoTestCase
{
    public function testSearchByGroup()
    {
        $threads[0] = $this->mockDataObject(array('courseId' => 1, 'taskId' => 1, 'userId' => 1, 'isElite' => 127));
        $threads[1] = $this->mockDataObject(array('courseId' => 1, 'taskId' => 1, 'userId' => 2, 'isElite' => 0));
        $threads[2] = $this->mockDataObject(array('courseId' => 2, 'taskId' => 1, 'userId' => 3, 'isElite' => 1));

        $testConditions = array(
            array(
                'condition' => array('courseId' => 1),
                'expectedResults' => array($threads[0], $threads[1]),
                'expectedCount' => 2
            ),
            array(
                'condition' => array('taskId' => 1),
                'expectedResults' => array($threads[0], $threads[1], $threads[2]),
                'expectedCount' => 3
            ),
            array(
                'condition' => array('userId' => 2),
                'expectedResults' => array($threads[1]),
                'expectedCount' => 1
            ),
            array(
                'condition' => array('isElite' => 127),
                'expectedResults' => array($threads[0]),
                'expectedCount' => 1
            ),
            array(
                'condition' => array('courseIds' => array(1, 2)),
                'expectedResults' => array($threads[0], $threads[1], $threads[2]),
                'expectedCount' => 3
            ),
            array(
                'condition' => array('content' => '啥'),
                'expectedResults' => array(),
                'expectedCount' => 0
            )
        );

        $this->searchByGroupTestUtil($testConditions, $this->getCompareKeys());
    }

    public function testCountByGroup()
    {
        $threads[0] = $this->mockDataObject(array('courseId' => 1, 'taskId' => 1, 'userId' => 1, 'isElite' => 127));
        $threads[1] = $this->mockDataObject(array('courseId' => 1, 'taskId' => 1, 'userId' => 2, 'isElite' => 0));
        $threads[2] = $this->mockDataObject(array('courseId' => 2, 'taskId' => 1, 'userId' => 3, 'isElite' => 1));

        $res[0] = $this->getDao()->countByGroup(array('courseId' => 1), 'userId');
        $res[1] = $this->getDao()->countByGroup(array('taskId' => 1), 'courseId');
        $res[2] = $this->getDao()->countByGroup(array('content' => '？'));
        
        $this->assertEquals(array(
            array('userId' => 1, 'count' => 1),
            array('userId' => 2, 'count' => 1)
        ), $res[0]);
        $this->assertEquals(array(
            array('courseId' => 1, 'count' => 2),
            array('courseId' => 2, 'count' => 1)
        ), $res[1]);
        $this->assertEquals(array(
            array('count' => 3)
        ), $res[2]);
    }

    public function testDeleteByThreadId()
    {
        $res[0] = $this->mockDataObject(array('threadId' => 1));
        $res[1] = $this->mockDataObject(array('threadId' => 1));
        $res[2] = $this->mockDataObject(array('threadId' => 2));

        $this->assertGreaterThan(0, $this->getDao()->deleteByThreadId(1));
        $this->assertEquals(0, $this->getDao()->deleteByThreadId(1));
        $this->assertGreaterThan(0, $this->getDao()->deleteByThreadId(2));
        $this->assertEquals(0, $this->getDao()->deleteByThreadId(2));
    }

    protected function searchByGroupTestUtil($testConditons, $testFields, $groupBy = null)
    {
        foreach ($testConditons as $testConditon) {
            $count = $this->getDao()->count($testConditon['condition']);
            $this->assertEquals($count, $testConditon['expectedCount']);
            $orderBy = empty($testConditon['orderBy']) ? array() : $testConditon['orderBy'];
            $results = $this->getDao()->searchByGroup($testConditon['condition'], $orderBy, 0, 10);
            foreach ($results as $key => $result) {
                $this->assertArrayEquals($result, $testConditon['expectedResults'][$key], $testFields, $groupBy);
            }
        }
    }
    
    protected function getDefaultMockFields()
    {
        return array(
            'courseId' => rand(0, 1000),
            'taskId' => rand(0, 1000),
            'threadId' => rand(0, 1000),
            'userId' => rand(0, 1000),
            'isElite' => rand(0, 127),
            'content' => '哈？'
        );
    }
}
