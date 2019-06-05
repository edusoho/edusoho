<?php

namespace Tests\Unit\Notification\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class NotificationBatchDaoTest extends BaseDaoTestCase
{
    protected function getDefaultMockFields()
    {
        return array(
            'sn' => 'test12345',
            'eventId' => 1,
        );
    }
}
