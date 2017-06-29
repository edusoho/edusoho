<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\FriendDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class FriendDaoImpl extends GeneralDaoImpl implements FriendDao
{
    protected $table = 'friend';

    public function updateByFromIdAndToId($fromId, $toId, $fields)
    {
        return $this->db()->update($this->table, $fields, array('fromId' => $fromId, 'toId' => $toId));
    }

    public function getByFromIdAndToId($fromId, $toId)
    {
        return $this->getByFields(array('fromId' => $fromId, 'toId' => $toId));
    }

    public function searchByFromId($fromId, $start, $limit)
    {
        return $this->search(array('fromId' => $fromId), array('createdTime' => 'DESC'), $start, $limit);
    }

    public function findFollowingsByFromId($fromId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE fromId = ? ORDER BY createdTime DESC ";

        return $this->db()->fetchAll($sql, array($fromId));
    }

    public function findFollowersByToId($toId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE toId = ? ORDER BY createdTime DESC ";

        return $this->db()->fetchAll($sql, array($toId));
    }

    public function searchByToId($toId, $start, $limit)
    {
        return $this->search(array('toId' => $toId), array('createdTime' => 'DESC'), $start, $limit);
    }

    public function findByFromIdAndToIds($fromId, array $toIds)
    {
        if (empty($toIds)) {
            return array();
        }

        $toIds = array_values($toIds);
        $marks = str_repeat('?,', count($toIds) - 1).'?';
        $parmaters = array_merge(array($fromId), $toIds);
        $sql = "SELECT * FROM {$this->table} WHERE fromId = ? AND toId IN ({$marks});";

        return $this->db()->fetchAll($sql, $parmaters);
    }

    public function searchByUserId($userId, $start, $limit)
    {
        return $this->search(array('fromId' => $userId, 'pair' => 1), array('createdTime' => 'DESC'), $start, $limit);
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'fromId = :fromId',
                'toId = :toId',
                'pair = :pair',
            ),
            'orderbys' => array('createdTime'),
        );
    }
}
