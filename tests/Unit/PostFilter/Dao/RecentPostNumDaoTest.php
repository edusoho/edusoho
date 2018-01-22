<?php

namespace Tests\Unit\PostFilter\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class RecentPostNumDaoTest extends BaseDaoTestCase
{
    public function testGetByIpAndType()
    {
        $recentPostNum = $this->getDao()->create($this->getDefaultMockFields());
        $result = $this->getDao()->getByIpAndType('127.0.0.1', 'type');
        $this->assertEquals(2, $result['num']);
    }

    protected function getDefaultMockFields()
    {
        return array('ip' => '127.0.0.1', 'type' => 'type', 'num' => 2, 'createdTime' => 0, 'updatedTime' => 0);
    }
}
