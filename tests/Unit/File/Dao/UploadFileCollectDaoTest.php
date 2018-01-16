<?php

namespace Tests\Unit\File\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class UploadFileCollectDaoTest extends BaseDaoTestCase
{
    public function testGetByUserIdAndFileId()
    {
        $collection = $this->mockDataObject();
        $result = $this->getDao()->getByUserIdAndFileId(2, 2);
        $this->assertEquals(2, $result['fileId']);
    }

    public function testFindByUserIdAndFileIds()
    {
        $collection = $this->mockDataObject();
        $result = $this->getDao()->findByUserIdAndFileIds(array(), 2);
        $this->assertEquals(array(), $result);

        $result = $this->getDao()->findByUserIdAndFileIds(array(1, 2), 2);
        $this->assertEquals(2, $result[0]['fileId']);
    }

    public function testFindByUserId()
    {
        $collection1 = $this->mockDataObject(array('userId' => 3));
        $collection2 = $this->mockDataObject(array('fileId' => 3));
        $result = $this->getDao()->findByUserId(2);
        $this->assertEquals(3, $result[0]['fileId']);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'fileId' => 2,
            'userId' => 2,
            'createdTime' => 0,
            'updatedTime' => 0,
        );
    }
}
