<?php

namespace Tests\Course\Dao;

use Tests\Base\BaseDaoTestCase;

class ThreadDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        for ($i = 0; $i < 10; $i++) {
            $expected[] = $this->mockDataObject();
        }

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 10
            ),
            array(
                'condition' => array('courseId' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 10
            ),
            array(
                'condition' => array('courseSetId' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 10
            ),
            array(
                'condition' => array('taskId' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 10
            ),
            array(
                'condition' => array('userId' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 10
            ),
            array(
                'condition' => array('type' => 'discussion'),
                'expectedResults' => $expected,
                'expectedCount' => 10
            ),
            array(
                'condition' => array('types' => array('discussion', 'question')),
                'expectedResults' => $expected,
                'expectedCount' => 10
            ),
            array(
                'condition' => array('isStick' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 10
            ),
            array(
                'condition' => array('isElite' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 10
            ),
            array(
                'condition' => array('postNum' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 10
            ),
            array(
                'condition' => array('postNumLargerThan' => 0),
                'expectedResults' => $expected,
                'expectedCount' => 10
            ),
            array(
                'condition' => array('title' => '哼'),
                'expectedResults' => $expected,
                'expectedCount' => 10
            ),
            array(
                'condition' => array('content' => '爱上地方'),
                'expectedResults' => $expected,
                'expectedCount' => 10
            ),
            array(
                'condition' => array('courseIds', array(1, 2)),
                'expectedResults' => $expected,
                'expectedCount' => 10
            ),
            array(
                'condition' => array('private' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 10
            )
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    protected function mockDataObject($fields = array())
    {
        return $this->getDao()->create(array_merge($this->getDefaultMockFields(), $fields));
    }

    protected function getDefaultMockFields()
    {
        return array(
            'courseId' => 1,
            'taskId' => 1,
            'userId' => 1,
            'type' => 'discussion',
            'isStick' => 1,
            'isElite' => 1,
            'isClosed' => 1,
            'private' => 1,
            'title' => '嗯哼？',
            'content' => '爱上地方',
            'postNum' => 1,
            'hitNum' => 1,
            'followNum' => 1,
            'latestPostTime' => time(),
            'courseSetId' => 1
        );
    }
}
