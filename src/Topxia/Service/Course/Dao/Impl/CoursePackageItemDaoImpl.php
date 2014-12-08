<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CoursePackageItemDao;
use PDO;

class CoursePackageItemDaoImpl extends BaseDao implements CoursePackageItemDao
{
    protected $table = 'course_package_item';

    public function getRelation($id)
    {
       $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
       return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findRelationsByPackgeId($packageId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE packageId = ?";
        return $this->getConnection()->fetchAll($sql, array($packageId)) ? : array();
    }

    public function addRelation($relation)
    {
        $affected = $this->getConnection()->insert($this->table, $relation);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course package item error.');
        }
        return $this->getRelation($this->getConnection()->lastInsertId());
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

}