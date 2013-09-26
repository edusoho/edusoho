<?php

namespace Topxia\Service\Activity\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Activity\Dao\ActivityMaterialDao;

class ActivityMaterialDaoImpl extends BaseDao implements ActivityMaterialDao
{
    protected $table = 'activity_material';

    public function getMaterial($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findMaterialsByActivityId($courseId, $start, $limit)
    {
        $sql ="SELECT * FROM {$this->table} WHERE activityId=? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($courseId)) ? : array();
    }


    public function getMaterialCountByActivityId($courseId)
    {
        $sql ="SELECT COUNT(*) FROM {$this->table} WHERE activityId = ?";
        return $this->getConnection()->fetchColumn($sql, array($courseId));
    }

    public function addMaterial($material)
    {
        $affected = $this->getConnection()->insert($this->table, $material);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert learn error.');
        }
        return $this->getMaterial($this->getConnection()->lastInsertId());
    }

    public function deleteMaterial($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }
}