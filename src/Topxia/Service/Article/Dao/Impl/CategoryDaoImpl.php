<?php

namespace Topxia\Service\Article\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Article\Dao\CategoryDao;

class CategoryDaoImpl extends BaseDao implements CategoryDao
{
    protected $table = 'article_category';

    public function addCategory($category)
    {
        $affected = $this->getConnection()->insert($this->table, $category);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert category error.');
        }

        $this->clearCached();
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

    public function getCategoryByParentId($parentId)
    {
        $that = $this;

        return $this->fetchCached("parentId:{$parentId}:limit:1", $parentId, function ($parentId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE parentId = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($parentId));
        }

        );
    }

    public function findAllCategoriesByParentId($parentId)
    {
        $that = $this;

        return $this->fetchCached("parentId:{$parentId}", $parentId, function ($parentId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE parentId = ? order by weight";
            return $that->getConnection()->fetchAll($sql, array($parentId));
        }

        );
    }

    public function findAllPublishedCategoriesByParentId($parentId)
    {
        $that = $this;

        return $this->fetchCached("parentId:{$parentId}:published:1", $parentId, function ($parentId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE parentId = ? AND published = ? order by weight";
            return $that->getConnection()->fetchAll($sql, array($parentId, 1));
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

    public function findCategoriesByParentId($parentId, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE parentId = ? ORDER BY {$orderBy} DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($parentId)) ?: array();
    }

    public function findCategoriesCountByParentId($parentId)
    {
        $that = $this;

        return $this->fetchCached("parentId:{$parentId}:count", $parentId, function ($parentId) use ($that) {
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
            $sql = "SELECT * FROM {$that->getTable()} ORDER BY weight ASC";
            return $that->getConnection()->fetchAll($sql) ?: array();
        }

        );
    }
}
