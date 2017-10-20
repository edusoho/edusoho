<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\MessageDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MessageDaoImpl extends GeneralDaoImpl implements MessageDao
{
    protected $table = 'message';

    // protected function _createQueryBuilder($conditions)
    // {
    //     $conditions = array_filter($conditions, function ($v) {
    //         if ($v === 0) {
    //             return true;
    //         }

    //         if (empty($v)) {
    //             return false;
    //         }
    //         return true;
    //     });

    //     if (isset($conditions['content'])) {
    //         $conditions['content'] = "%{$conditions['content']}%";
    //     }
    //     return $this->_getQueryBuilder($conditions)
    //         ->from($this->table, 'message')
    //         ->andWhere('fromId = :fromId')
    //         ->andWhere('toId = :toId')
    //         ->andWhere('createdTime = :createdTime')
    //         ->andWhere('createdTime >= :startDate')
    //         ->andWhere('createdTime < :endDate')
    //         ->andWhere('fromId IN (:fromIds)')
    //         ->andWhere('toId IN (:toIds)')
    //         ->andWhere('content LIKE :content');
    // }

    // public function count($conditions)
    // {
    //     $builder = $this->_createQueryBuilder($conditions)
    //         ->select('COUNT(id)');

    //     return $builder->execute()->fetchColumn(0);
    // }

    // public function search($conditions, $orderBy, $start, $limit)
    // {
    //     $this->filterStartLimit($start, $limit);

    //     $builder = $this->_createQueryBuilder($conditions)
    //         ->select('*')
    //         ->setFirstResult($start)
    //         ->setMaxResults($limit)
    //         ->orderBy('createdTime', 'DESC');

    //     return $builder->execute()->fetchAll() ?: array();
    // }

    public function getByFromIdAndToId($fromId, $toId)
    {
        return $this->getByFields(array('fromId' => $fromId, 'toId' => $toId));
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function deleteByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "DELETE FROM {$this->table} WHERE id IN ({$marks});";

        return $this->db()->executeUpdate($sql, $ids);
    }

    public function declares()
    {
        return array(
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'isDelete = :isDelete',
                'fromId = :fromId',
                'toId = :toId',
                'createdTime = :createdTime',
                'createdTime >= :startDate',
                'createdTime < :endDate',
                'fromId IN (:fromIds)',
                'toId IN (:toIds)',
                'content LIKE :content',
            ),
        );
    }
}
