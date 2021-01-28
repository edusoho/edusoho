<?php

namespace Biz\Task\Dao\Impl;

use Biz\Task\Dao\TryViewLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TryViewLogDaoImpl extends GeneralDaoImpl implements TryViewLogDao
{
    protected $table = 'course_task_try_view';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array(),
            'conditions' => array(
                'courseId = :courseId',
                'createdTime >= :createdTime_GE',
                'createdTime <= :createdTime_LE',
            ),
        );
    }

    public function searchLogCountsByConditionsGroupByCreatedTimeWithFormat($conditions, $format = '%Y-%m-%d')
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("COUNT(id) as count, from_unixtime(createdTime, '{$format}') as date")
            ->from($this->table, $this->table)
            ->groupBy('date')
            ->orderBy('date', 'ASC');

        return $builder->execute()->fetchAll();
    }
}
