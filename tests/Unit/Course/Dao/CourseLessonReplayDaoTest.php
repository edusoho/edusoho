<?php

namespace Tests\Unit\Course\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class CourseLessonReplayDaoTest extends BaseDaoTestCase
{
    public function testDeleteByLessonId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('lessonId' => 2));

        $this->getDao()->deleteByLessonId(1);

        $res = array();
        $res[] = $this->getDao()->findByLessonId(1);
        $res[] = $this->getDao()->findByLessonId(2);

        $this->assertEquals(array(), $res[0]);
        $this->assertEquals(array($expected[2]), $res[1]);
    }

    public function testFindByLessonId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('lessonId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByLessonId(1);
        $res[] = $this->getDao()->findByLessonId(2);

        $this->assertEquals(array($expected[0], $expected[1]), $res[0]);
        $this->assertEquals(array($expected[2]), $res[1]);
    }

    public function testDeleteByCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('lessonId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2));

        $this->getDao()->deleteByCourseId(1);

        $res = array();
        $res[] = $this->getDao()->findByLessonId(1);
        $res[] = $this->getDao()->findByLessonId(2);

        $this->assertEquals(array($expected[2]), $res[0]);
        $this->assertEquals(array(), $res[1]);
    }

    // 假设约束条件可以确定唯一的记录
    public function testGetByCourseIdAndLessonId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('lessonId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2));

        $res = array();
        $res[] = $this->getDao()->getByCourseIdAndLessonId(1, 1);
        $res[] = $this->getDao()->getByCourseIdAndLessonId(1, 2);
        $res[] = $this->getDao()->getByCourseIdAndLessonId(2, 1);

        foreach ($expected as $key => $val) {
            $this->assertEquals($val, $res[$key]);
        }
    }

    public function testFindByCourseIdAndLessonId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('lessonId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByCourseIdAndLessonId(1, 1);
        $res[] = $this->getDao()->findByCourseIdAndLessonId(1, 2);

        $this->assertEquals(array($expected[0], $expected[1]), $res[0]);
        $this->assertEquals(array($expected[2]), $res[1]);
    }

    public function testUpdateByLessonId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();

        $tmp = $this->mockDataObject(array('lessonId' => 2));
        $tmp['lessonId'] = '1';
        $expected[] = $tmp;

        $this->getDao()->updateByLessonId(2, 'live', array('lessonId' => 1));

        $res = array();
        $res[] = $this->getDao()->findByLessonId(1);
        $res[] = $this->getDao()->findByLessonId(2);

        $this->assertEquals(array($expected[0], $expected[1]), $res[0]);
        $this->assertEquals(array(), $res[1]);
    }

    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array());
        $expected[] = $this->mockDataObject(array());
        $expected[] = $this->mockDataObject(array('lessonId' => 2));

        $testConditions = array(
            array(
                'condition' => array('courseId' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('lessonId' => 1),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('hidden' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('copyId' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('type' => 'live'),
                'expectedResults' => $expected,
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
            'copyId' => 1,
        );
    }
}
