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
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert category error.');
        }

        return $this->getCategory($this->getConnection()->lastInsertId());
    }

    public function deleteCategory($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function getCategory($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id));
        }

        );
    }

    public function findCategoryByCode($code)
    {
        $that = $this;

        return $this->fetchCached("code:{$code}", $code, function ($code) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE code = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($code));
        }

        );
    }

    public function updateCategory($id, $category)
    {
        $this->getConnection()->update($this->table, $category, array('id' => $id));
        $this->clearCached();
        return $this->getCategory($id);
    }

    public function findCategoriesByGroupId($groupId)
    {
        $that = $this;

        return $this->fetchCached("groupId:{$groupId}", $groupId, function ($groupId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE groupId = ? ORDER BY weight ASC";
            return $that->getConnection()->fetchAll($sql, array($groupId)) ?: array();
        }

        );
    }

    public function findCategoriesByGroupIdAndOrgId($groupId, $orgId)
    {
        $that = $this;

        return $this->fetchCached("groupId:{$groupId}orgId:{$orgId}", $groupId, $orgId, function ($groupId, $orgId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE groupId = ? AND orgId =?  ORDER BY weight ASC";
            return $that->getConnection()->fetchAll($sql, array($groupId, $orgId)) ?: array();
        }

        );
    }

    public function findCategoriesByParentId($parentId, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE parentId = ? ORDER BY {$orderBy} DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($parentId)) ?: array();
    }

    public function findAllCategoriesByParentId($parentId)
    {
        $that = $this;

        return $this->fetchCached("parentId:{$parentId}:order:weigth:asc", $parentId, function ($parentId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE parentId = ? ORDER BY weight ASC";
            return $that->getConnection()->fetchAll($sql, array($parentId)) ?: array();
        }

        );
    }

    public function findCategoriesByGroupIdAndParentId($groupId, $parentId)
    {
        $that = $this;

        return $this->fetchCached("groupId:{$groupId}:parentId:{$parentId}", $groupId, $parentId, function ($groupId, $parentId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE groupId = ? AND parentId = ? ORDER BY weight ASC";
            return $that->getConnection()->fetchAll($sql, array($groupId, $parentId)) ?: array();
        }

        );
    }

    public function findCategoriesCountByParentId($parentId)
    {
        $that = $this;

        return $this->fetchCached("parentId:{$parentId}", $parentId, function ($parentId) use ($that) {
            $sql = "SELECT COUNT(*) FROM {$that->getTable()} WHERE  parentId = ?";
            return $that->getConnection()->fetchColumn($sql, array($parentId));
        }

        );
    }

    public function findCategoriesByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids) ?: array();
    }

    public function findAllCategories()
    {
        $that = $this;

        return $this->fetchCached("all", function () use ($that) {
            $sql = "SELECT * FROM {$that->getTable()}";
            return $that->getConnection()->fetchAll($sql) ?: array();
        }

        );
    }
}
