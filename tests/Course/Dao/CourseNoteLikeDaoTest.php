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
        ;
    }

    protected function getDefaultMockFields()
    {
        return array(
            'noteId' => 1,
            'userId' => 1
        );
    }
}
