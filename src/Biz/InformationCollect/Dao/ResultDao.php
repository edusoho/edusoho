<?php

namespace Biz\InformationCollect\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ResultDao extends AdvancedDaoInterface
{
    public function getByUserIdAndEventId($userId, $eventId);

    public function findByUserIdsAndEventId($userIds, $eventId);

    public function countGroupByEventId($eventIds);
}
