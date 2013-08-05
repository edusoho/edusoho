<?php
namespace Topxia\Service\Content\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Content\Dao\NavigationDao;

class NavigationDaoImpl extends BaseDao implements NavigationDao
{
    protected $table = 'navigation';

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
    
    public function getTopNavigationsCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE type = 'top' ";
        return $this->getConnection()->fetchColumn($sql, array());
    }

    public function getFootNavigationsCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE type = 'foot' ";
        return $this->getConnection()->fetchColumn($sql, array());
    }

    public function getNavigationsCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        return $this->getConnection()->fetchColumn($sql, array());
    }

    public function findTopNavigations($start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE type = 'top' ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }

    public function findFootNavigations($start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE type = 'foot' ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }

    public function findNavigations($start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }

}