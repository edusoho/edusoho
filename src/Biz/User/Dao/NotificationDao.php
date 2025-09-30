<?php

namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface NotificationDao extends AdvancedDaoInterface
{
    public function searchByUserId($userId, $start, $limit);

    public function findBatchIdsByUserIdAndType($userId, $type);
}
