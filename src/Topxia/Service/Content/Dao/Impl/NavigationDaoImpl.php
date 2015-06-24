<?php
namespace Topxia\Service\Content\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Content\Dao\NavigationDao;

class NavigationDaoImpl extends BaseDao implements NavigationDao
{
    protected $table = 'navigation';

    public function getNavigationsCountByType($type, $isOpen = null)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  type = ?";
        if(is_null($isOpen)){
            return $this->getConnection()->fetchColumn($sql, array($type));       
        }else{
            $sql = $sql." and isOpen = ?";
            return $this->getConnection()->fetchColumn($sql, array($type, $isOpen));
        }
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
        return ($this->getConnection()->delete($this->table, array('id' => $id))); 
    }

    public function deleteNavigationByParentId($parentId)
    {
        return ($this->getConnection()->delete($this->table, array('parentId' => $parentId))); 
    }
    
    public function getNavigationsCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        return $this->getConnection()->fetchColumn($sql, array());
    }

    public function findNavigationsByType($type, $start, $limit, $isOpen = null)
    {
        $this->filterStartLimit($start, $limit);
        if(is_null($isOpen)){
            $sql = "SELECT * FROM {$this->table} WHERE type = ? ORDER BY sequence ASC LIMIT {$start}, {$limit}";
            return $this->getConnection()->fetchAll($sql, array($type));
        }else{
            $sql = "SELECT * FROM {$this->table} WHERE type = ? and isOpen = ? ORDER BY sequence ASC LIMIT {$start}, {$limit}";
            return $this->getConnection()->fetchAll($sql, array($type,$isOpen));
        }
    }

    public function findNavigations($start, $limit, $isOpen = null)
    {
        $this->filterStartLimit($start, $limit);
        if(is_null($isOpen)){
            $sql = "SELECT * FROM {$this->table} ORDER BY sequence ASC LIMIT {$start}, {$limit}";
            return $this->getConnection()->fetchAll($sql, array());
        }else{
            $sql = "SELECT * FROM {$this->table} where isOpen = ? ORDER BY sequence ASC LIMIT {$start}, {$limit}";
            return $this->getConnection()->fetchAll($sql, array($isOpen));
        }
    }

}