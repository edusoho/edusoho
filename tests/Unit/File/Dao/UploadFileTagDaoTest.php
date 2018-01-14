<?php

namespace Tests\Unit\File\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class UploadFileTagDaoTest extends BaseDaoTestCase
{
    public function testDeleteByFileId()
    {
        $expected = $this->mockDataObject();
        $this->getDao()->deleteByFileId(2);
        $result = $this->getDao()->findByFileId(2);
        $this->assertEquals(array(), $result);
    }

    public function testDeleteByTagId()
    {
        $expected = $this->mockDataObject();
        $this->getDao()->deleteByTagId(3);
        $result = $this->getDao()->findByTagId(3);
        $this->assertEquals(array(), $result);
    }

    public function testFindByFileId()
    {
        $expected = $this->mockDataObject();
        $result = $this->getDao()->findByFileId(2);
        $this->assertArrayEquals($expected, $result[0], $this->getCompareKeys());
    }

    public function testFindByTagId()
    {
        $expected = $this->mockDataObject();
        $result = $this->getDao()->findByTagId(3);
        $this->assertArrayEquals($expected, $result[0], $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return array(
            'fileId' => 2,
            'tagId' => 3,
        );
    }
}
