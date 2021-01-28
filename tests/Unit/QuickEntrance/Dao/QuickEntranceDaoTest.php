<?php

namespace Tests\Unit\QuickEntrance\Dao;

use Biz\BaseTestCase;
use Biz\QuickEntrance\Dao\QuickEntranceDao;

class QuickEntranceDaoTest extends BaseTestCase
{
    public function testGetByUserId()
    {
        $result = $this->getQuickEntranceDao()->getByUserId($this->getCurrentUser()->getId());

        $this->assertNull($result);

        $fields = array(
            'userId' => (int) $this->getCurrentUser()->getId(),
            'data' => array(
                'test_entrance_code_1',
                'test_entrance_code_2',
            ),
        );

        $expected = $this->getQuickEntranceDao()->create($fields);

        $result = $this->getQuickEntranceDao()->getByUserId($this->getCurrentUser()->getId());

        $this->assertEquals($expected, $result);
    }

    /**
     * @return QuickEntranceDao
     */
    private function getQuickEntranceDao()
    {
        return $this->createDao('QuickEntrance:QuickEntranceDao');
    }
}
