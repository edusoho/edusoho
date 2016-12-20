<?php
namespace Biz\RefererLog\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\RefererLog\Dao\OrderRefererLogDao;

class OrderRefererLogDaoImpl extends GeneralDaoImpl implements OrderRefererLogDao
{
    protected $table = 'order_referer_log';

    public function declares()
    {
        return array(
            'orderbys'   => array('createdTime', 'recommendedSeq', 'studentNum', 'hitNum'),
            'conditions' => array(
                "id IN ( :ids )",
                'refererLogId = :refererLogId',
                'refererLogId IN (:refererLogIds)',
                'targetId = :targetId',
                'targetType = :targetType',
                'sourceTargetId = :sourceTargetId',
                'sourceTargetType = :sourceTargetType',
                'createdTime >= :startTime',
                'createdTime <= :endTime'
            )
        );
    }

    public function searchOrderRefererLogs($conditions, $orderBy, $start, $limit, $groupBy)
    {
        $this->filterStartLimit($start, $limit);

        $seachFields = '*';
        if (!empty($groupBy)) {
            $seachFields = 'id,orderId,targetId,targetType,COUNT(id) AS buyNum';
        }

        $builder = $this->_createSearchQueryBuilder($conditions, $orderBy, $groupBy)
            ->select($seachFields)
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function countOrderRefererLogs($conditions, $groupBy)
    {
        $builder = $this->_createSearchQueryBuilder($conditions, array('createdTime', 'DESC'), $groupBy)
            ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function countDistinctOrderRefererLogs($conditions, $distinctField)
    {
        $builder = $this->_createSearchQueryBuilder($conditions, array('createdTime', 'DESC'), array())
            ->select("COUNT(DISTINCT({$distinctField}))");

        return $builder->execute()->fetchColumn(0);
    }

    protected function _createSearchQueryBuilder($conditions, $orderBy, $groupBy)
    {
        $builder = parent::_createQueryBuilder($conditions);

        for ($i = 0; $i < count($orderBy); $i = $i + 2) {
            $builder->addOrderBy($orderBy[$i], $orderBy[$i + 1]);
        };

        if (!empty($groupBy)) {
            $builder->groupBy($groupBy);
        }

        return $builder;
    }
}
