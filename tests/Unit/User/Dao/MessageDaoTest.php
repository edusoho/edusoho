<?php

namespace Tests\Unit\User\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class MessageDaoTest extends BaseDaoTestCase
{
    public function testGetByFromIdAndToId()
    {
        $this->mockDataObject();
        $result = $this->getDao()->getByFromIdAndToId(1, 2);

        $this->assertEquals(1, $result['fromId']);
        $this->assertEquals(2, $result['toId']);
    }

    public function testFindByIds()
    {
        $create = $this->mockDataObject();
        $result = $this->getDao()->findByIds(array($create['id']));
        $this->assertEquals(1, count($result));
    }

    public function testDeleteByIds()
    {
        $create = $this->mockDataObject();
        $result = $this->getDao()->findByIds(array($create['id']));
        $this->assertEquals(1, count($result));
        $this->getDao()->deleteByIds(array($create['id']));
        $result = $this->getDao()->findByIds(array($create['id']));
        $this->assertEquals(0, count($result));
    }

    public function testDeleteByIdsWithEmpty()
    {
        $delete = $this->getDao()->deleteByIds(array());
        $this->assertEquals(array(), $delete);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'fromId' => 1,
            'toId' => 2,
            'content' => 'test content',
        );
    }
}
