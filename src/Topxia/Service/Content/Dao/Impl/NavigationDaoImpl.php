<?php
namespace Topxia\Service\Content\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Content\Dao\NavigationDao;

class NavigationDaoImpl extends BaseDao implements NavigationDao
{
    protected $table = 'navigation';

    public function getNavigationsCountByType($type)
    {
        $that = $this;

        return $this->fetchCached("type:{$type}", $type, function ($type) use ($that) {
            $sql = "SELECT COUNT(*) FROM {$that->getTable()} WHERE  type = ?";
            return $that->getConnection()->fetchColumn($sql, array($type));
        }

        );
    }

    public function getNavigation($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        );
    }

    public function addNavigation($navigation)
    {
        $affected = $this->getConnection()->insert($this->table, $navigation);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert navigation error.');
        }

        return $this->getConnection()->lastInsertId();
    }

    public function updateNavigation($id, $fields)
    {
        $result = $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function deleteNavigation($id)
    {
        $result = ($this->getConnection()->delete($this->table, array('id' => $id)));
        $this->clearCached();
        return $result;
    }

    public function deleteNavigationByParentId($parentId)
    {
        $result = ($this->getConnection()->delete($this->table, array('parentId' => $parentId)));
        $this->clearCached();
        return $result;
    }

    public function getNavigationsCount()
    {
        $that = $this;

        return $this->fetchCached("count", function () use ($that) {
            $sql = "SELECT COUNT(*) FROM {$that->getTable()}";
            return $that->getConnection()->fetchColumn($sql, array());
        }

        );
    }

    public function findNavigationsByType($type, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);

        $that = $this;

        return $this->fetchCached("type:{$type}:start:{$start}:limit:{$limit}", $type, $start, $limit, function ($type, $start, $limit) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE type = ? ORDER BY sequence ASC LIMIT {$start}, {$limit}";
            return $that->getConnection()->fetchAll($sql, array($type));
        }

        );
    }

    public function findNavigations($start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} ORDER BY sequence ASC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }

    public function searchNavigationCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchNavigations($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        if (empty($conditions['orgId'])) {
            unset($conditions['orgId']);
        }

        if (isset($conditions['likeOrgCode'])) {
            unset($conditions['orgCode']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'org')
            ->andWhere('name = :name')
            ->andWhere('type = :type')
            ->andWhere('isOpen = :isOpen')
            ->andWhere('isNewWin =:isNewWin')
            ->andWhere('orgId = :orgId')
            ->andWhere('orgCode = :orgCode')
            ->andWhere('orgCode LIKE :likeOrgCode');
        return $builder;
    }
}
