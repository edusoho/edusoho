<?php

namespace Biz\Coupon\Dao\Impl;

use Biz\Coupon\Dao\CouponDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class CouponDaoImpl extends AdvancedDaoImpl implements CouponDao
{
    protected $table = 'coupon';

    public function declares()
    {
        return array(
            'conditions' => array(
                'userId = :userId',
                'targetId = :targetId',
                'targetType = :targetType',
                'batchId = :batchId',
                'batchId IN ( :batchIds)',
                'batchId <> :batchIdNotEqual',
                'type = :type',
                'status = :status',
                'createdTime >= :startDateTime',
                'createdTime < :endDateTime',
                'code LIKE :codeLike',
                'orderTime >= :useStartDateTime',
                'orderTime < :useEndDateTime',
                'id IN ( :ids)',
            ),
            'timestamps' => array('createdTime'),
            'serializes' => array(
                'targetIds' => 'delimiter',
            ),
            'orderbys' => array(
                'createdTime',
                'orderTime',
                'id',
            ),
        );
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function getByCode($code, array $options = array())
    {
        $lock = isset($options['lock']) && true === $options['lock'];
        $sql = "SELECT * FROM {$this->table} WHERE code = ? LIMIT 1".($lock ? ' FOR UPDATE' : '');

        return $this->db()->fetchAssoc($sql, array($code)) ?: null;
    }

    public function findByBatchId($batchId, $start, $limit)
    {
        return $this->search(
            array(
                'batchId' => $batchId,
            ),
            array(
                'createdTime' => 'DESC',
            ),
            $start,
            $limit
        );
    }

    public function deleteByBatch($id)
    {
        return $this->db()->delete($this->table, array('batchId' => $id));
    }

    protected function createQueryBuilder($conditions)
    {
        $tmpConditions = array();

        if (isset($conditions['batchIdNotEqual'])) {
            $tmpConditions['batchIdNotEqual'] = $conditions['batchIdNotEqual'];
        }

        if (!isset($conditions['userId'])) {
            $conditions = array_filter($conditions);
        }

        $conditions = array_merge($conditions, $tmpConditions);

        if (isset($conditions['code'])) {
            $conditions['codeLike'] = "%{$conditions['code']}%";
            unset($conditions['code']);
        }

        if (isset($conditions['startDateTime'])) {
            $conditions['startDateTime'] = strtotime($conditions['startDateTime']."\n00:00:00");
        }

        if (isset($conditions['endDateTime'])) {
            $conditions['endDateTime'] = strtotime($conditions['endDateTime'].'+1 day');
        }

        if (isset($conditions['useStartDateTime'])) {
            $conditions['useStartDateTime'] = strtotime($conditions['useStartDateTime']."\n00:00:00");
        }

        if (isset($conditions['useEndDateTime'])) {
            $conditions['useEndDateTime'] = strtotime($conditions['useEndDateTime'].'+1 day');
        }

        return parent::createQueryBuilder($conditions);
    }
}
