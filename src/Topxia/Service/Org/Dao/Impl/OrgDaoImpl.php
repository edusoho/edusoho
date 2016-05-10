<?php
namespace Topxia\Service\Org\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Org\Dao\OrgDao;

/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 5/9/16
 * Time: 19:54
 */
class OrgDaoImpl extends BaseDao implements OrgDao
{
    protected $table = 'org';

    public function createOrg($org)
    {
        $affected = $this->getConnection()->insert($this->getTable(), $org);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert org error.');
        }

        return $this->getOrg($this->getConnection()->lastInsertId());
    }

    public function updateOrg($id, $fields)
    {
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
        $sql = "DELETE  FROM {$this->getTable()} where orgCode like ?";
        return $this->getConnection()->executeUpdate($sql, array($orgCode));
    }

    public function findOrgTablelist()
    {
        $sql = "SELECT * FROM {$this->getTable()}";
        return $this->getConnection()->fetchAll($sql, array()) ?: null;
    }

    public function getOrgByCode($value)
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE  code = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($value)) ?: null;
    }
}
