<?php

namespace Tests\Course\Dao;

use Tests\Base\BaseDaoTestCase;

class CourseNoteLikeDaoTest extends BaseDaoTestCase
{
    public function testGetByNoteIdAndUserId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('noteId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->getByNoteIdAndUserId(1, 1);
        $res[] = $this->getDao()->getByNoteIdAndUserId(2, 1);
        $res[] = $this->getDao()->getByNoteIdAndUserId(1, 2);

        foreach ($factor as $key => $val) {
            $this->assertEquals($factor[$key], $res[$key]);
        }
    }

    public function testDeleteByNoteIdAndUserId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('noteId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2));

        $this->getDao()->deleteByNoteIdAndUserId(1, 1);

        $res = $this->getDao()->getByNoteIdAndUserId(1, 1);

        $this->assertNull($res);
    }

    public function testFindByUserId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('noteId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByUserId(1);
        $res[] = $this->getDao()->findByUserId(2);

        $this->assertEquals(array($factor[0], $factor[1]), $res[0]);
        $this->assertEquals(array($factor[2]), $res[1]);
    }

    public function testFindByNoteId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('noteId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByNoteId(1);
        $res[] = $this->getDao()->findByNoteId(2);

        $this->assertEquals(array($factor[0], $factor[2]), $res[0]);
        $this->assertEquals(array($factor[1]), $res[1]);
    }

    public function testFindByNoteIds()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('noteId' => 2));
        $factor[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByNoteIds(array(1));
        $res[] = $this->getDao()->findByNoteIds(array(1, 2));

        $this->assertEquals(array($factor[0], $factor[2]), $res[0]);
        $this->assertEquals($factor, $res[1]);
    }

    public function testFindByNoteIdsAndUserId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('noteId' => 2));
        $factor[] = $this->mockDataObject(array('noteId' => 3, 'userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByNoteIdsAndUserId(array(1, 2, 3), 1);
        $res[] = $this->getDao()->findByNoteIdsAndUserId(array(1), 2);
        $res[] = $this->getDao()->findByNoteIdsAndUserId(array(3), 2);

        $this->assertEquals(array($factor[0], $factor[1]), $res[0]);
        $this->assertEquals(array(), $res[1]);
        $this->assertEquals(array($factor[2]), $res[2]);
    }

    public function testDeleteByNoteId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('noteId' => 2));
        $factor[] = $this->mockDataObject(array('noteId' => 3, 'userId' => 2));

        $this->getDao()->deleteByNoteId(1);

        $res = $this->getDao()->findByNoteIds(array(1, 2, 3));

        $this->assertEquals(array($factor[2], $factor[3]), $res);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'noteId' => 1,
            'userId' => 1
        );
    }
}
