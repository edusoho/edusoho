<?php

namespace Tests\Unit\Notification\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class NotificationEventDaoTest extends BaseDaoTestCase
{
    public function testFindByEventIds()
    {
        $result = $this->getNotificationEventDao()->findByEventIds(array());
        $this->assertEmpty($result);

        $event = $this->getDefaultMockFields();
        $event = $this->getNotificationEventDao()->create($event);
        $result = $this->getNotificationEventDao()->findByEventIds(array($event['id']));
        $this->assertEquals(10, $result[0]['totalCount']);
    }

    public function testDeclares()
    {
        $result = $this->createDao('Notification:NotificationEventDao')->declares();
        $declare = array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'createdTime', 'updatedTime'),
            'conditions' => array(
                'id = :id',
            ),
        );

        $this->assertArrayEquals($declare, $result);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'title' => '测试通知事件',
            'content' => '消息主体',
            'totalCount' => 10,
        );
    }

    private function getNotificationEventDao()
    {
        return $this->createDao('Notification:NotificationEventDao');
    }
}
