<?php

namespace MaterialLib\Service\MaterialLib\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use MaterialLib\Service\MaterialLib\Dao\MaterialLibDao;

class MaterialLibDaoImpl extends BaseDao implements MaterialLibDao
{
    public function findLatestUploadCourses($limit)
    {
        $this->filterStartLimit(null, $limit);
        $sql = "SELECT c.* FROM course AS c WHERE id IN (SELECT DISTINCT(targetId) FROM upload_files ORDER BY createdTime DESC) LIMIT 0, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($limit)) ? : array();
    }

    public function findLatestUploadUsers($limit)
    {
        $this->filterStartLimit(null, $limit);
        $sql = "SELECT u.* FROM user AS u WHERE id IN (SELECT DISTINCT(createdUserId) FROM upload_files ORDER BY createdTime DESC) LIMIT 0, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($limit)) ? : array();
    }

    public function findFilesByUserId($userId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM upload_files WHERE createdUserId = ? AND (globalId != 0 OR globalId = '') ORDER BY createdTime DESC LIMIT {$start},{$limit}";
        return $this->getConnection()->fetchAll($sql, array($userId));
    }

    public function findFilesByUserIds($userIds, $start, $limit)
    {
        if (empty($userIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($userIds) - 1).'?';
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM upload_files WHERE createdUserId IN ({$marks}) AND (globalId != 0 OR globalId = '') ORDER BY createdTime DESC LIMIT {$start},{$limit}";
        return $this->getConnection()->fetchAll($sql, $userIds);
    }
}
