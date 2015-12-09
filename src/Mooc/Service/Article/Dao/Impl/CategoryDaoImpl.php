<?php

namespace Mooc\Service\Article\Dao\Impl;

use Mooc\Service\Article\Dao\CategoryDao;
use Topxia\Service\Article\Dao\Impl\CategoryDaoImpl as BaseCategoryDaoImpl;

class CategoryDaoImpl extends BaseCategoryDaoImpl implements CategoryDao
{
    public function findAllCategories()
    {
        $sql = "SELECT * FROM {$this->table} WHERE branchSchoolId =  0 ORDER BY weight ASC";
        return $this->getConnection()->fetchAll($sql) ?: array();
    }

    public function findCategoriesByBranchSchoolId($branchSchoolId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE branchSchoolId = ? ORDER BY weight ASC";
        return $this->getConnection()->fetchAll($sql, array($branchSchoolId)) ?: array();
    }
}
