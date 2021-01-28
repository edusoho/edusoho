<?php

namespace Biz\Notification\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface NotificationEventDao extends GeneralDaoInterface
{
    public function findByEventIds($ids);
}
