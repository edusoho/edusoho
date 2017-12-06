<?php

namespace Tests\Unit\Thread\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ThreadDaoTest extends BaseDaoTestCase
{
    public function testFindThreadIds()
    {
        $this->mockDataObject();
        $this->mockDataObject();
        $this->mockDataObject(array('userId' => 2));
        $this->assertEquals(2, count($this->getDao()->findThreadIds(array('userId' => 1, 'targetType' => 'classroom'))));
    }

    protected function mockDataObject($fields = array())
    {
        return $this->getDao()->create(array_merge($this->getDefaultMockFields(), $fields));
    }

    protected function getDefaultMockFields()
    {
        return array(
            'targetType' => 'classroom',
            'targetId' => 1,
            'userId' => 1,
            'type' => 'discussion',
            'sticky' => 1,
            'title' => '嗯哼？',
            'content' => '爱上地方',
            'postNum' => 1,
            'hitNum' => 1,
            'memberNum' => 1,
            'lastPostTime' => time(),
        );
    }
}
