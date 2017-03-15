<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\UserFieldDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserFieldDaoImpl extends GeneralDaoImpl implements UserFieldDao
{
    protected $table = 'user_field';

    public function getByFieldName($fieldName)
    {
        return $this->getByFields(array('fieldName' => $fieldName));
    }

    public function getFieldsOrderBySeq()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY seq";

        return $this->db()->fetchAll($sql) ?: array();
    }

    public function getEnabledFieldsOrderBySeq()
    {
        $sql = "SELECT * FROM {$this->table} where enabled=1 ORDER BY seq";

        return $this->db()->fetchAll($sql) ?: array();
    }

    protected function createQueryBuilder($conditions)
    {
        if (isset($conditions['fieldName'])) {
            $conditions['fieldName'] = '%'.$conditions['fieldName'].'%';
        }

        return parent::createQueryBuilder($conditions);
    }

    public function declares()
    {
        return array(
            'orderbys' => array('seq'),
            'conditions' => array(
                'enabled = :enabled',
                'fieldName like :fieldName',
            ),
        );
    }
}
