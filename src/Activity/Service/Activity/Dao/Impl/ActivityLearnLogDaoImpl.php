<?php

namespace Activity\Service\Activity\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Activity\Service\Activity\Dao\ActivityLearnLogDao;

class ActivityLearnLogDaoImpl extends BaseDao implements ActivityLearnLogDao
{
    protected $table = 'activity_learn_log';

    public function get($id)
    {
        $sql            = "SELECT * FROM {$this->getTable()} WHERE id = ? LIMIT 1";
        $result         = $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        $result['data'] = $this->_jsonUnserialize($result['data']);
        return $result;
    }

    private function _jsonUnserialize($value)
    {
        if (empty($value)) {
            return array();
        }

        return json_decode($value, true);
    }

    private function _jsonSerialize($value)
    {
        if (empty($value)) {
            return '';
        }

        return json_encode($value);
    }

    public function add($log)
    {
        $log['createdTime'] = time();
        $log['data']        = $this->_jsonSerialize($log['data']);
        $affected           = $this->getConnection()->insert($this->table, $log);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert log error.');
        }

        return $this->get($this->getConnection()->lastInsertId());
    }
}
