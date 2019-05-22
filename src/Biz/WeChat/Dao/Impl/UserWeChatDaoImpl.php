<?php

namespace Biz\WeChat\Dao\Impl;

use Biz\WeChat\Dao\UserWeChatDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class UserWeChatDaoImpl extends AdvancedDaoImpl implements UserWeChatDao
{
    protected $table = 'user_wechat';

    public function declares()
    {
        return array(
            'orderbys' => array('createdTime', 'updatedTime', 'lastRefreshTime'),
            'conditions' => array(
                'id = :id',
                'appId = :appId',
                'userId = :userId',
                'type= :type',
                'openId = :openId',
                'unionId = :unionId',
                'subscribe = :subscribe',
                'openId IN (:openIds)',
                'lastRefreshTime = :lastRefreshTime',
                'lastRefreshTime < :lastRefreshTime_LT',
                'lastRefreshTime > :lastRefreshTime_GT',
            ),
            'timestamps' => array(
                'createdTime',
                'updatedTime',
            ),
            'serializes' => array(
                'data' => 'json',
            ),
        );
    }

    public function findByUserId($userId)
    {
        return $this->findByFields(array('userId' => $userId));
    }

    public function findByUserIdAndType($userId, $type)
    {
        return $this->findByFields(array('userId' => $userId, 'type' => $type));
    }

    public function getByUserIdAndType($userId, $type)
    {
        return $this->getByFields(array('userId' => $userId, 'type' => $type));
    }

    public function findOpenIdsInListsByType($openIds, $type)
    {
        if (empty($openIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($openIds) - 1).'?';
        $sql = "SELECT openId FROM {$this->table} WHERE type = ? AND openId IN ({$marks})";

        return $this->db()->fetchAll($sql, array_merge(array($type), $openIds)) ?: array();
    }
}
