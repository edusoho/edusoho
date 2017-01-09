<?php
namespace Biz\Org\Dao\Impl;

use Biz\Org\Dao\OrgDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class OrgDaoImpl extends GeneralDaoImpl implements OrgDao
{
    protected $table = 'org';

    public function declares()
    {
        $declares['conditions'] = array(
            'id = :id',
            'parentId = :parentId',
            'depth = :depth',
        );

        $declares['timestamps'] = array(
            'createdTime'
        );

        $declares['orderbys'] = array(
            'createdTime',
            'updatedTime'
        );

        $declares['timestamps'] = array(
            'createdTime',
            'updateTime'
        );

        return $declares;
    }

    public function deleteByPrefixOrgCode($orgCode)
    {
        $likeOrgCode = $orgCode."%";
        $sql         = "DELETE  FROM {$this->table()} where orgCode like ? ";
        return $this->db()->executeUpdate($sql, array($likeOrgCode));
    }

    public function findByPrefixOrgCode($orgCode)
    {
        $sql   = "SELECT * FROM {$this->table()}";
        $query = array();

        if (!empty($orgCode)) {
            $sql .= " WHERE orgCode like ?  order by orgCode ";
            $query = array($orgCode.'%');
        } else {
            $sql .= " order by orgCode";
        }

        return $this->db()->fetchAll($sql, $query) ?: array();
    }

    public function getByOrgCode($orgCode)
    {
        return $this->getByFields(array('orgCode' => $orgCode));
    }

    public function getByCode($code)
    {
        return $this->getByFields(array('code' => $code));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByNameAndParentId($name, $parentId)
    {
        return $this->getByFields(array('name' => $name, 'parentId' => $parentId));
    }
}
