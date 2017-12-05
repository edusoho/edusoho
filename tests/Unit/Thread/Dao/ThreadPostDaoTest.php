<?php

namespace Tests\Unit\Thread\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ThreadPostDaoTest extends BaseDaoTestCase
{
    public function testFindThreadIds()
    {
        $this->mockDataObject();
        $this->mockDataObject();
        $this->mockDataObject(array('userId' => 2));
        $this->assertEquals(1, count($this->getDao()->foundThreadIds(array('userId' => 2, 'targetType' => 'classroom'))));
    }

    protected function mockDataObject($fields = array())
    {
        return $this->getDao()->create(array_merge($this->getDefaultMockFields(), $fields));
    }

    protected function getDefaultMockFields()
    {
        return array(
            'targetType' => 'classroom',
            'targetId' => rand(0, 1000),
            'threadId' => rand(0, 1000),
            'userId' => rand(0, 1000),
            'content' => '哈？',
        );
    }
}
