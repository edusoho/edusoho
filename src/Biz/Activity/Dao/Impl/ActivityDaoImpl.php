<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\ActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ActivityDaoImpl extends GeneralDaoImpl implements ActivityDao
{
    protected $table = 'activity';

    public function findByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? LIMIT 1";
        return $this->db()->fetchAll($sql, array($courseId)) ?: array();
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
        $declares['conditions'] = array(
            'fromCourseId = :fromCourseId',
            'mediaType = :mediaType'
        );

        return $declares;
    }

}
