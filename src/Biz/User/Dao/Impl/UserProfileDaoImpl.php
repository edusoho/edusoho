<?php

namespace Biz\User\Dao\Impl;

use Biz\Common\CommonException;
use Biz\User\Dao\UserProfileDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserProfileDaoImpl extends GeneralDaoImpl implements UserProfileDao
{
    protected $table = 'user_profile';

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function dropFieldData($fieldName)
    {
        $fieldNames = array(
            'intField1',
            'intField2',
            'intField3',
            'intField4',
            'intField5',
            'dateField1',
            'dateField2',
            'dateField3',
            'dateField4',
            'dateField5',
            'floatField1',
            'floatField2',
            'floatField3',
            'floatField4',
            'floatField5',
            'textField1',
            'textField2',
            'textField3',
            'textField4',
            'textField5',
            'textField6',
            'textField7',
            'textField8',
            'textField9',
            'textField10',
            'varcharField1',
            'varcharField2',
            'varcharField3',
            'varcharField4',
            'varcharField5',
            'varcharField6',
            'varcharField7',
            'varcharField8',
            'varcharField9',
            'varcharField10', );

        if (!in_array($fieldName, $fieldNames)) {
            throw CommonException::ERROR_PARAMETER();
        }

        $sql = "UPDATE {$this->table} set {$fieldName} =null ";
        $result = $this->db()->exec($sql);

        return $result;
    }

    public function findDistinctMobileProfiles($start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE `mobile` <> '' GROUP BY `mobile` ORDER BY `id` ASC";
        $sql = $this->sql($sql, array(), $start, $limit);

        return $this->db()->fetchAll($sql);
    }

    protected function createQueryBuilder($conditions)
    {
        if (isset($conditions['mobile'])) {
            $conditions['mobile'] = "%{$conditions['mobile']}%";
        }

        if (isset($conditions['qq'])) {
            $conditions['qq'] = "{$conditions['qq']}%";
        }

        if (isset($conditions['keywordType']) && isset($conditions['keyword']) && 'truename' == $conditions['keywordType']) {
            $conditions['truename'] = "%{$conditions['keyword']}%";
        }

        if (isset($conditions['keywordType']) && isset($conditions['keyword']) && 'idcardLike' == $conditions['keywordType']) {
            $conditions['idcardLike'] = "%{$conditions['keyword']}%";
        }

        if (isset($conditions['idcard'])) {
            $conditions['idcard'] = trim($conditions['idcard']);
        }

        return parent::createQueryBuilder($conditions);
    }

    public function declares()
    {
        return array(
            'orderbys' => array('id'),
            'conditions' => array(
                'mobile LIKE :mobile',
                'truename LIKE :truename',
                'idcard LIKE :idcardLike',
                'idcard = :idcard',
                'id IN (:ids)',
                'mobile = :tel',
                'mobile <> :mobileNotEqual',
                'qq LIKE :qq',
            ),
        );
    }
}
