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
                'userId in (:userIds)',
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

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
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

    public function getByTypeAndUnionId($type, $unionId)
    {
        return $this->getByFields(array('type' => $type, 'unionId' => $unionId));
    }

    public function getByTypeAndOpenId($type, $openId)
    {
        return $this->getByFields(array('type' => $type, 'openId' => $openId));
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

    public function findSubscribedUsersByUserIdsAndType($userIds, $type)
    {
        if (empty($userIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($userIds) - 1).'?';
        $sql = "SELECT openId FROM {$this->table} WHERE type = ? AND userId IN ({$marks}) And isSubscribe = 1";

        return $this->db()->fetchAll($sql, array_merge(array($type), $userIds)) ?: array();
    }
}
