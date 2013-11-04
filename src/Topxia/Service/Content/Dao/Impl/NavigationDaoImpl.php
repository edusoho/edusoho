<?php
namespace Topxia\Service\Content\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Content\Dao\NavigationDao;

class NavigationDaoImpl extends BaseDao implements NavigationDao
{
    protected $table = 'navigation';

    public function getNavigationsCountByType($type)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  type = ?";
        return $this->getConnection()->fetchColumn($sql, array($type));
    }

    public function getNavigation($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addNavigation($navigation)
    {
        $affected = $this->getConnection()->insert($this->table, $navigation);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert navigation error.');
        }

        return $this->getConnection()->lastInsertId();
    }

    public function updateNavigation($id, $fields)
    {
        return $this->getConnection()->update($this->table, $fields, array('id' => $id));
    }

    public function deleteNavigation($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }
    
    public function getNavigationsCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        return $this->getConnection()->fetchColumn($sql, array());
    }

    public function findNavigationsByType($type, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE type = ? ORDER BY sequence ASC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($type));
    }

    public function findNavigations($start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} ORDER BY sequence ASC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }

}