<?php
namespace Biz\User\Dao\Impl;

use Biz\User\Dao\UserFieldDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserFieldDaoImpl extends GeneralDaoImpl implements UserFieldDao
{
    protected $table = "user_field";

    public function getByFieldName($fieldName)
    {
        return $this->getByFields(array('fieldName' => $fieldName));
    }

    public function count($condition)
    {
        $builder = $this->_createSearchQueryBuilder($condition)
            ->select('count(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function getAllFieldsOrderBySeq()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY seq";
        return $this->getConnection()->fetchAll($sql) ?: array();
    }

    public function getAllFieldsOrderBySeqAndEnabled()
    {
        $sql = "SELECT * FROM {$this->table} where enabled=1 ORDER BY seq";
        return $this->getConnection()->fetchAll($sql) ?: array();
    }

    protected function _createSearchQueryBuilder($condition)
    {
        if (isset($condition['fieldName'])) {
            $condition['fieldName'] = "%".$condition['fieldName']."%";
        }

        $builder = $this->createDynamicQueryBuilder($condition)
            ->from($this->table, $this->table)
            ->andWhere('enabled = :enabled')
            ->andWhere('fieldName like :fieldName');

        return $builder;
    }

    public function declares()
    {
        return array(
        );
    }
}
