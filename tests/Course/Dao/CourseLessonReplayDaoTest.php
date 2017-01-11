<?php

namespace Tests\Course\Dao;

use Tests\Base\BaseDaoTestCase;

class CourseLessonReplayDaoTest extends BaseDaoTestCase
{
    public function testDeleteByLessonId()
    {
        $factor = array();
        $factor[] = $mockDataObject();
        $factor[] = $mockDataObject();
        $factor[] = $mockDataObject(array('lessonId' => 2));

        $this->getDao()->deleteByLessonId(1);

        $res = array();
        $res[] = findByLessonId(1);
        $res[] = findByLessonId(2);

        $this->assertEquals(array(), $res[0]);
        $this->assertEquals(array($factor[2]), $res[1]);
    }

    public function testFindByLessonId()
    {
        $factor = array();
        $factor[] = $mockDataObject();
        $factor[] = $mockDataObject();
        $factor[] = $mockDataObject(array('lessonId' => 2));

        $res = array();
        $res[] = findByLessonId(1);
        $res[] = findByLessonId(2);

        $this->assertEquals(array($factor[0], $factor[1]), $res[0]);
        $this->assertEquals(array($factor[2]), $res[1]);
    }

    public function testDeleteByCourseId()
    {
        $factor = array();
        $factor[] = $mockDataObject();
        $factor[] = $mockDataObject(array('lessonId' => 2));
        $factor[] = $mockDataObject(array('course' => 2));

        $this->getDao()->deleteByCourseId(1);

        $res = array();
        $res[] = findByLessonId(1);
        $res[] = findByLessonId(2);

        $this->assertEquals(array($factor[2]), $res[0]);
        $this->assertEquals(array(), $res[1]);
    }

    // 假设约束条件可以确定唯一的记录
    public function testGetByCourseIdAndLessonId()
    {
        $factor = array();
        $factor[] = $mockDataObject();
        $factor[] = $mockDataObject(array('lessonId' => 2));
        $factor[] = $mockDataObject(array('course' => 2));

        $res = array();
        $res[] = $this->getDao()->getByCourseIdAndLessonId(1, 1);
        $res[] = $this->getDao()->getByCourseIdAndLessonId(1, 2);
        $res[] = $this->getDao()->getByCourseIdAndLessonId(2, 1);

        foreach ($factor as $key => $val) {
            $this->assertEquals($val, $res[$key]);
        }
    }

    public function testFindByCourseIdAndLessonId()
    {
        $factor = array();
        $factor[] = $mockDataObject();
        $factor[] = $mockDataObject();
        $factor[] = $mockDataObject(array('lessonId' => 2));

        $res = array();
        $res[] = $this->getDao()->getByCourseIdAndLessonId(1, 1);
        $res[] = $this->getDao()->getByCourseIdAndLessonId(1, 2);

        $this->assertEquals(array($factor[0], $factor[1]), $res[0]);
        $this->assertEquals(array($factor[2]), $res[1]);
    }

    public function testUpdateByLessonId()
    {
        $factor = array();
        $factor[] = $mockDataObject();
        $factor[] = $mockDataObject(array('lessonId' => 2));

        $this->getDao()->updateByLessonId(2, array('lessonId' => 1));

        $res = array();
        $res[] = $this->getDao()->findByLessonId(1);
        $res[] = $this->getDao()->findByLessonId(2);

        $this->assertEquals(array($factor[0], $factor[1]), $res[0]);
        $this->assertEquals(array(), $res[1]);
    }

    public function testSearch()
    {
        $factor = array();
        $factor[] = $mockDataObject(array());
        $factor[] = $mockDataObject(array());
        $factor[] = $mockDataObject(array('lessonId' => 2));

        $testConditions = array(
            array(
                'condition' => array('courseId' => 1),
                'expectedResults' => $factor,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('lessonId' => 1),
                'expectedResults' => array($factor[0], $factor[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('hidden' => 1),
                'expectedResults' => $factor,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('copyId' => 1),
                'expectedResults' => $factor,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('type' => 'live'),
                'expectedResults' => $factor,
                'expectedCount' => 3,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return array(
            'lessonId' => 1,
            'courseId' => 1,
            'title' => 'a',
            'replayId' => 1,
            'globalId' => 1,
            'userId' => 1,
            'hidden' => 1,
            'type' => 'live',
            'copyId' => 1
        );
    }
}
