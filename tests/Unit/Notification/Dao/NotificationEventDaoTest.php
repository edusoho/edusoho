<?php

namespace Tests\Unit\Notification\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class NotificationEventDaoTest extends BaseDaoTestCase
{
    protected function getDefaultMockFields()
    {
        return array(
            'title' => '测试通知事件',
            'content' => '消息主体',
            'totalCount' => 10,
        );
    }
}
