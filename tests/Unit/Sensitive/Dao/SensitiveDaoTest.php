<?php

namespace Tests\Unit\Sensitive\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class SensitiveDaoTest extends BaseDaoTestCase
{
    public function testGetByName()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['name' => 'ccc']);
        $result = $this->getDao()->getByName('ccc');
        $this->assertArrayEquals($expected[1], $result, $this->getCompareKeys());
    }

    public function testFindAllKeywords()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['name' => 'ccc', 'createdTime' => 2000]);
        $result = $this->getDao()->findAllKeywords();
        $this->assertEquals(2, count($result));
    }

    public function testFindByState()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['name' => 'ccc', 'state' => 'banned']);
        $result = $this->getDao()->findByState('banned');
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return [
            'state' => 'replaced',
            'name' => 'aaa',
            'bannedNum' => 2,
            'createdTime' => 0,
        ];
    }
}
