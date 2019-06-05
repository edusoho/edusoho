<?php

namespace Biz\Notification\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface NotificationStrategyDao extends GeneralDaoInterface
{
    public function findByEventId($eventId);
}
