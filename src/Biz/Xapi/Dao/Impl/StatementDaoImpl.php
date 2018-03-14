<?php

namespace Biz\Xapi\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Biz\Xapi\Dao\StatementDao;

class StatementDaoImpl extends AdvancedDaoImpl implements StatementDao
{
    protected $table = 'xapi_statement';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time'),
            'orderbys' => array(
                'created_time',
                'push_time',
            ),
            'serializes' => array(
                'context' => 'json',
                'data' => 'json',
            ),
            'conditions' => array(
                'status = :status',
            ),
        );
    }

    public function callbackStatusPushedAndPushedTimeByUuids(array $ids, $pushTime)
    {
        if (empty($ids)) {
            return array();
        }

        $params = array_merge(array('pushed', $pushTime), $ids);
        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "UPDATE {$this->table} SET status = ?,push_time = ? WHERE uuid IN ({$marks})";

        return $this->db()->executeUpdate($sql, $params);
    }

    public function retryStatusPushingToCreatedByCreatedTime($createdTime)
    {
        $sql = "UPDATE {$this->table} SET status = 'created' WHERE created_time > ? AND status = 'pushing'";

        return $this->db()->executeUpdate($sql, array($createdTime));
    }
}
