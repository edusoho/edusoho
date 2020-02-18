<?php

namespace Tests\Unit\DestroyAccount\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class DestroyAccountRecordDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $record1 = $this->mockDataObject(array('nickname' => 'test1'));
        $record2 = $this->mockDataObject(array('nickname' => 'test2'));
        $result = $this->getDao()->search(array('id' => 1), array(), 0, 10);
        $this->assertEquals('test1', $result[0]['nickname']);

        $result = $this->getDao()->search(array('nickname' => 'test'), array(), 0, 10);
        $this->assertEquals(2, count($result));
        $this->assertEquals('test1', $result[0]['nickname']);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'ip' => '127.0.0.1',
            'userId' => 1,
            'nickname' => 'test',
            'status' => 'audit',
        );
    }
}
