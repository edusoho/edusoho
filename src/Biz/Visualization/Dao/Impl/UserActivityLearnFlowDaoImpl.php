<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\UserActivityLearnFlowDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class UserActivityLearnFlowDaoImpl extends AdvancedDaoImpl implements UserActivityLearnFlowDao
{
    protected $table = 'user_activity_learn_flow';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'serializes' => [
            ],
            'conditions' => [
                'id = :id',
            ],
            'orderbys' => ['id', 'createdTime', 'startTime', 'lastLearnTime'],
        ];
    }

    public function getByUserIdAndSign($userId, $sign)
    {
        return $this->getByFields(['userId' => $userId, 'sign' => $sign]);
    }

    public function setUserOtherFlowUnActive($userId, $activeSign)
    {
        $sql = "UPDATE {$this->table} SET active = 0 WHERE userId = ? AND sign != ?;";

        return $this->db()->executeUpdate($sql, [$userId, $activeSign]);
    }

    public function getUserLatestActiveFlow($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND active = 1 ORDER BY lastLearnTime DESC LIMIT 1;";

        return $this->db()->fetchAssoc($sql, [$userId]);
    }
}
