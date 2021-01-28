<?php

namespace Biz\Sign\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Sign\Dao\SignUserStatisticsDao;

class SignUserStatisticsDaoImpl extends GeneralDaoImpl implements SignUserStatisticsDao
{
    protected $table = 'sign_user_statistics';

    public function declares()
    {
    }

    public function getStatisticsByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ?  AND targetType = ? AND targetId = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($userId, $targetType, $targetId)) ?: null;
    }
}
