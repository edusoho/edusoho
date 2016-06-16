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
        $this->clearCached();
        return $this->getOrg($id);
    }

    public function getOrg($id)
    {
        $that = $this;
        return $this->fetchCached("orgId:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        });
    }

    public function findOrgsByIds($ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "SELECT * FROM {$this->getTable()} WHERE id IN ({$marks});";

        // var_dump($sql, $marks, $ids);
        $org = $this->getConnection()->fetchAll($sql, $ids);
        return $org;
    }

    public function delete($id)
    {
        $result = $this->getConnection()->delete($this->getTable(), array('id' => $id));
        $this->clearCached();
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
        $that = $this;
        return $this->fetchCached("orgCode:{$orgCode}", $orgCode, function ($orgCode) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE orgCode = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($orgCode)) ?: array();
        });
    }

    public function getOrgByCode($code)
    {
        $that = $this;
        return $this->fetchCached("code:{$code}", $code, function ($code) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE  code = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($code)) ?: array();
        });
    }
}
