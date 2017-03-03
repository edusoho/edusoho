<?php

namespace Tests\Unit\Course\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class CourseMaterialDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('lessonId' => 2, 'fileId' => 2));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('id' => 1),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('courseId' => 1),
                'expectedResults' => array($expected[0], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('courseSetId' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('lessonId' => 1),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('excludeLessonId' => 1),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('type' => 'eeee'),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('userId' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('titleLike' => 's'),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('copyId' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('fileId' => 1),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('fileIds' => array(1, 2)),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('source' => 'aaaa'),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('sources' => array('aaaa', 1)),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('courseIds' => array(1, 2)),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testFindByCopyIdAndLockedCourseIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('copyId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByCopyIdAndLockedCourseIds(1, array());
        $res[] = $this->getDao()->findByCopyIdAndLockedCourseIds(1, array(1));
        $res[] = $this->getDao()->findByCopyIdAndLockedCourseIds(2, array(2));
        $res[] = $this->getDao()->findByCopyIdAndLockedCourseIds(1, array(1, 2));

        $this->assertEquals(array(), $res[0]);
        $this->assertEquals(array($expected[0]), $res[1]);
        $this->assertEquals(array(), $res[2]);
        $this->assertEquals(array($expected[0], $expected[2]), $res[3]);
    }

    public function testDeleteByLessonId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('lessonId' => 2));

        $this->getDao()->deleteByLessonId(1, 'eeee');
        $this->getDao()->deleteByLessonId(2, 'aaaa');

        $res = $this->getDao()->findByCopyIdAndLockedCourseIds(1, array(1));

        $this->assertEquals(array($expected[2]), $res);
    }

    public function testDeleteByCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('lessonId' => 2));

        $this->getDao()->deleteByCourseId(1, 'eeee');
        $this->getDao()->deleteByCourseId(2, 'aaaa');

        $res = $this->getDao()->findByCopyIdAndLockedCourseIds(1, array(1, 2));

        $this->assertEquals(array($expected[1]), $res);
    }

    public function testFindMaterialsByLessonIdAndSource()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('lessonId' => 2));
        $expected[] = $this->mockDataObject(array('source' => 'bbbb'));

        $res = array();
        $res[] = $this->getDao()->findMaterialsByLessonIdAndSource(1, 'aaaa');
        $res[] = $this->getDao()->findMaterialsByLessonIdAndSource(2, 'aaaa');
        $res[] = $this->getDao()->findMaterialsByLessonIdAndSource(1, 'bbbb');

        foreach ($expected as $key => $val) {
            $this->assertEquals(array($val), $res[$key]);
        }
    }

    public function testDeleteByCourseSetId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseSetId' => 2));

        $this->getDao()->deleteByCourseSetId(1, 'eeee');
        $this->getDao()->deleteByCourseSetId(2, 'aaaa');

        $res = $this->getDao()->findByCopyIdAndLockedCourseIds(1, array(1));

        $this->assertEquals(array($expected[2]), $res);
    }

    public function testDeleteByFileId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('fileId' => 2));

        $this->getDao()->deleteByFileId(1);

        $res = $this->getDao()->findByCopyIdAndLockedCourseIds(1, array(1));

        $this->assertEquals(array($expected[2]), $res);
    }

    public function testSearchDistinctFileIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('fileId' => 2));
        $expected[] = $this->mockDataObject(array('fileId' => 3));

        $toBeCompared = array();
        foreach ($expected as $key => $val) {
            $toBeCompared[] = array('fileId' => $val['fileId'], 'createdTime' => $val['createdTime']);
        }

        $res = $this->getDao()->searchDistinctFileIds(array(), array(), 0, 10);

        $this->assertEquals(array($toBeCompared[1], $toBeCompared[2], $toBeCompared[3]), $res);
    }

    public function testCountGroupByFileId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('title' => 'k'));
        $expected[] = $this->mockDataObject(array('fileId' => 2));

        $res = array();
        $res[] = $this->getDao()->countGroupByFileId(array('title' => 'k'));
        $res[] = $this->getDao()->countGroupByFileId(array());

        $this->assertEquals(1, $res[0]);
        $this->assertEquals(2, $res[1]);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'courseId' => 1,
            'lessonId' => 1,
            'title' => 'asdf',
            'link' => 'baidu.com',
            'fileId' => 1,
            'fileUri' => '\a\b',
            'fileMime' => 'ffff',
            'fileSize' => 1,
            'source' => 'aaaa',
            'userId' => 1,
            'copyId' => 1,
            'type' => 'eeee',
            'courseSetId' => 1,
        );
    }
}
