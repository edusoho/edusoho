<?php

namespace Tests\Unit\Notification\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class NotificationBatchDaoTest extends BaseDaoTestCase
{
    public function testDeclares()
    {
        $result = $this->createDao('Notification:NotificationBatchDao')->declares();
        $declare = array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'createdTime', 'updatedTime'),
            'conditions' => array(
                'id = :id',
                'eventId = :eventId',
                'sn = :sn',
                'status = :status',
                'strategyId = :strategyId',
            ),
        );

        $this->assertArrayEquals($declare, $result);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'sn' => 'test12345',
            'eventId' => 1,
        );
    }
}
