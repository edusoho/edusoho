<?php

namespace MaterialLib\Service\MaterialLib\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use MaterialLib\Service\MaterialLib\Dao\MaterialLibDao;

class MaterialLibDaoImpl extends BaseDao implements CourseDao
{
    public function findLatestUploadCourses($limit)
    {
        $this->filterStartLimit(null, $limit);
        $sql = "SELECT c.* FROM course AS c WHERE id IN (SELECT DISTINCT(targetId) FROM upload_files ORDER BY createdTime DESC) LIMIT 0, {$limit}";
        return $this->fetchAll($sql, array($limit)) ? : array();
    }

    public function findLatestUploadUsers($limit)
    {
        $this->filterStartLimit(null, $limit);
        $sql = "SELECT c.* FROM course AS c WHERE id IN (SELECT DISTINCT(createdUserId) FROM upload_files ORDER BY createdTime DESC) LIMIT 0, {$limit}";
        return $this->fetchAll($sql, array($limit)) ? : array();
    }
}
