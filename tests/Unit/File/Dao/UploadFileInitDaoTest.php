<?php

namespace Tests\Unit\File\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class UploadFileInitDaoTest extends BaseDaoTestCase
{
    public function testGetByGlobalId()
    {
        $file = $this->mockDataObject();
        $result = $this->getDao()->getByGlobalId('id');
        $this->assertEquals(2, $result['targetId']);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'globalId' => 'id',
            'targetId' => 2, 
            'storage' => 'local',
            'createdUserId' => 2,
            'createdTime' => 0,
        );
    }
}