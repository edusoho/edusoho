<?php
namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserFieldDao;

class UserFieldDaoImpl extends BaseDao implements UserFieldDao
{   
    protected $table="user_field";

    public function addField($field)
    {
        $affected = $this->getConnection()->insert($this->table, $field);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert user_field error.');
        }
        return $this->getField($this->getConnection()->lastInsertId());

    }

    public function getField($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getFieldByFieldName($fieldName)
    {
        $sql = "SELECT * FROM {$this->table} WHERE fieldName = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($fieldName)) ? : null ;
    }

    public function searchFieldCount($condition)
    {
        $builder = $this->_createSearchQueryBuilder($condition)
        ->select('count(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function getAllFieldsOrderBySeq()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY seq";
        return $this->getConnection()->fetchAll($sql) ? : array();
    }

    public function getAllFieldsOrderBySeqAndEnabled()
    {
        $sql = "SELECT * FROM {$this->table} where enabled=1 ORDER BY seq";
        return $this->getConnection()->fetchAll($sql) ? : array();
    }

    public function updateField($id,$fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getField($id);
    }

    public function deleteField($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    private function _createSearchQueryBuilder($condition)
    {   
        if(isset($condition['fieldName'])) $condition['fieldName']="%".$condition['fieldName']."%";

        $builder = $this->createDynamicQueryBuilder($condition)
            ->from($this->table, $this->table)
            ->andWhere('enabled = :enabled')
            ->andWhere('fieldName like :fieldName');

        return $builder;

    }

}