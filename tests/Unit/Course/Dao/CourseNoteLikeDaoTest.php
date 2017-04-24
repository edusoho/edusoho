<?php

namespace Tests\Unit\Course\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class CourseNoteLikeDaoTest extends BaseDaoTestCase
{
    public function testGetByNoteIdAndUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('noteId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->getByNoteIdAndUserId(1, 1);
        $res[] = $this->getDao()->getByNoteIdAndUserId(2, 1);
        $res[] = $this->getDao()->getByNoteIdAndUserId(1, 2);

        foreach ($expected as $key => $val) {
            $this->assertEquals($expected[$key], $res[$key]);
        }
    }

    public function testDeleteByNoteIdAndUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('noteId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $this->getDao()->deleteByNoteIdAndUserId(1, 1);

        $res = $this->getDao()->getByNoteIdAndUserId(1, 1);

        $this->assertEmpty($res);
    }

    public function testFindByUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('noteId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByUserId(1);
        $res[] = $this->getDao()->findByUserId(2);

        $param = $expected[1]['createdTime'] > $expected[0]['createdTime'] ?
            array($expected[1], $expected[0]) : array($expected[0], $expected[1]);
        $this->assertEquals($param, $res[0]);
        $this->assertEquals(array($expected[2]), $res[1]);
    }

    public function testFindByNoteId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('noteId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByNoteId(1);
        $res[] = $this->getDao()->findByNoteId(2);

        $param = $expected[2]['createdTime'] > $expected[0]['createdTime'] ?
            array($expected[2], $expected[0]) : array($expected[0], $expected[2]);
        $this->assertEquals($param, $res[0]);
        $this->assertEquals(array($expected[1]), $res[1]);
    }

    public function testFindByNoteIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('noteId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByNoteIds(array(1));
        $res[] = $this->getDao()->findByNoteIds(array(1, 2));

        $this->assertEquals(array($expected[0], $expected[2]), $res[0]);
        $this->assertEquals($expected, $res[1]);
    }

    public function testFindByNoteIdsAndUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('noteId' => 2));
        $expected[] = $this->mockDataObject(array('noteId' => 3, 'userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByNoteIdsAndUserId(array(1, 2, 3), 1);
        $res[] = $this->getDao()->findByNoteIdsAndUserId(array(1), 2);
        $res[] = $this->getDao()->findByNoteIdsAndUserId(array(3), 2);

        $this->assertEquals(array($expected[0], $expected[1]), $res[0]);
        $this->assertEquals(array(), $res[1]);
        $this->assertEquals(array($expected[2]), $res[2]);
    }

    public function testDeleteByNoteId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('noteId' => 2));
        $expected[] = $this->mockDataObject(array('noteId' => 3, 'userId' => 2));

        $this->getDao()->deleteByNoteId(1);

        $res = $this->getDao()->findByNoteIds(array(1, 2, 3));

        $this->assertEquals(array($expected[2], $expected[3]), $res);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'noteId' => 1,
            'userId' => 1,
        );
    }
}
