<?php

namespace Tests\Unit\DestroyAccount\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class DestroyedAccountDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $record1 = $this->mockDataObject(array('nickname' => 'a'));
        $record2 = $this->mockDataObject(array('nickname' => 'b'));
        $result = $this->getDao()->search(array('id' => 1), array(), 0, 10);
        $this->assertEquals('a', $result[0]['nickname']);

        $result = $this->getDao()->search(array('nicknameLike' => 'b'), array(), 0, 10);
        $this->assertEquals(1, count($result));
        $this->assertEquals('b', $result[0]['nickname']);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'recordId' => 1,
            'userId' => 1,
            'nickname' => 'test',
            'time' => 0,
        );
    }
}
