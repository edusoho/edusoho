<?php

namespace Biz\Task\Dao\Impl;

use Biz\Task\Dao\ViewLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ViewLogDaoImpl extends GeneralDaoImpl implements ViewLogDao
{
    protected $table = 'course_task_view';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array('name', 'createdTime'),
            'conditions' => array(
                'fileType = :fileType',
                'fileStorage = :fileStorage',
                'createdTime  >= :startTime',
                'createdTime < :endTime',
            ),
        );
    }

    public function searchGroupByTime($conditions, $startTime, $endTime)
    {
        $params = array($startTime, $endTime);

        $conditionStr = '';

        if (array_key_exists('fileType', $conditions) && !empty($conditions['fileType'])) {
            $conditionStr .= ' AND `fileType` = ? ';
            $params[] = $conditions['fileType'];
        }

        if (array_key_exists('fileStorage', $conditions) && !empty($conditions['fileStorage'])) {
            $conditionStr .= ' AND `fileStorage` = ? ';
            $params[] = $conditions['fileStorage'];
        }

        $sql = "SELECT count(`id`) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE  `createdTime`>=? AND `createdTime`<=? {$conditionStr} group by date_format(from_unixtime(`createdTime`),'%Y-%m-%d') order by date ASC ";

        return $this->db()->fetchAll($sql, $params);
    }
}
