<?php

namespace Topxia\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Taxonomy\Dao\CategoryDao;

class CategoryDaoImpl extends BaseDao implements CategoryDao 
{

	protected $table = 'category';

	public function addCategory($category) 
    {
		$affected = $this->getConnection()->insert($this->table, $category);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert category error.');
        }
        return $this->getCategory($this->getConnection()->lastInsertId());
	}

	public function deleteCategory($id) 
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
	}

	public function getCategory($id) 
    {
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
	}

	public function findCategoryByCode($code) 
    {
        $sql = "SELECT * FROM {$this->table} WHERE code = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($code));
	}

	public function updateCategory($id, $category) 
    {
        $this->getConnection()->update($this->table, $category, array('id' => $id));
        return $this->getCategory($id);
	}

	public function findCategoriesByGroupId($groupId) 
    {
        $sql = "SELECT * FROM {$this->table} WHERE groupId = ? ORDER BY weight ASC";
        return $this->getConnection()->fetchAll($sql, array($groupId)) ? : array();
    }

	public function findCategoriesByParentId($parentId, $orderBy = null, $start, $limit) 
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE parentId = ? ORDER BY {$orderBy} DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($parentId)) ? : array();
	}

    public function findCategoriesByGroupIdAndParentId($groupId, $parentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE groupId = ? AND parentId = ? ORDER BY weight ASC";
        return $this->getConnection()->fetchAll($sql, array($groupId, $parentId)) ? : array();
    }

	public function findCategoriesCountByParentId($parentId) 
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  parentId = ?";
        return $this->getConnection()->fetchColumn($sql, array($parentId));
	}

	public function findCategoriesByIds(array $ids) 
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids) ? : array();
    }

    public function findAllCategories()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->getConnection()->fetchAll($sql) ? : array();
    }

}