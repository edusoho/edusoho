<?php
namespace Org\Service\Org\Dao\Impl;

use Org\Service\Org\Dao\OrgDao;
use Topxia\Service\Common\BaseDao;

class OrgDaoImpl extends BaseDao implements OrgDao
{
    protected $table = 'org';

    public function createOrg($org)
    {
        $org['createdTime'] = time();

        $affected = $this->getConnection()->insert($this->getTable(), $org);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert org error.');
        }

        return $this->getOrg($this->getConnection()->lastInsertId());
    }

    public function updateOrg($id, $fields)
    {
        $fields['updateTime'] = time();

        $this->getConnection()->update($this->getTable(), $fields, array('id' => $id));
        return $this->getOrg($id);
    }

    public function getOrg($id)
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function delete($id)
    {
        $result = $this->getConnection()->delete($this->getTable(), array('id' => $id));
        return $result;
    }

    public function deleteOrgsByOrgCode($orgCode)
    {
        $likeOrgCode = $orgCode."%";
        $sql         = "DELETE  FROM {$this->getTable()} where orgCode like ? ";
        return $this->getConnection()->executeUpdate($sql, array($likeOrgCode));
    }

    public function findOrgsStartByOrgCode($orgCode)
    {
        $sql   = "SELECT * FROM {$this->getTable()}";
        $query = array();

        if (!empty($orgCode)) {
            $sql .= " WHERE orgCode like ?  order by orgCode ";
            $query = array($orgCode.'%');
        } else {
            $sql .= " order by orgCode";
        }

        return $this->getConnection()->fetchAll($sql, $query) ?: array();
    }

    public function getOrgByOrgCode($orgCode)
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE orgCode = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($orgCode)) ?: array();
    }

    public function getOrgByCode($value)
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE  code = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($value)) ?: array();
    }


     public function batchUpgradeOrgCodeAndOrgId($module, $id, $orgCode, $orgId){
        $sql = "UPDATE {$module} SET orgCode = ?, orgId = ? WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($orgCode, $orgId, $id));
     }

}
