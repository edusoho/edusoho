<?php

namespace Biz\User\Dao\Impl;

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
            'varcharField10');

        if (!in_array($fieldName, $fieldNames)) {
            throw $this->createDaoException('fieldName error');
        }

        $sql    = "UPDATE {$this->table} set {$fieldName} =null ";
        $result = $this->db()->exec($sql);
        return $result;
    }

    public function search($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->createProfileQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ?: array();
    }

    public function count($conditions)
    {
        $builder = $this->createProfileQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function findDistinctMobileProfiles($start, $limit)
    {
        // $start = (int) $start;
        // $limit = (int) $limit;

        $sql = "SELECT * FROM {$this->table} WHERE `mobile` <> '' GROUP BY `mobile` ORDER BY `id` ASC LIMIT {$start}, {$limit}";
        return $this->db()->fetchAll($sql);
    }

    private function createProfileQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($v) {
            if ($v === 0) {
                return true;
            }

            if (empty($v)) {
                return false;
            }

            return true;
        }

        );

        if (isset($conditions['mobile'])) {
            $conditions['mobile'] = "%{$conditions['mobile']}%";
        }

        if (isset($conditions['qq'])) {
            $conditions['qq'] = "{$conditions['qq']}%";
        }

        if (isset($conditions['keywordType']) && isset($conditions['keyword']) && $conditions['keywordType'] == 'truename') {
            $conditions['truename'] = "%{$conditions['keyword']}%";
        }

        if (isset($conditions['keywordType']) && isset($conditions['keyword']) && $conditions['keywordType'] == 'idcard') {
            $conditions['idcard'] = "%{$conditions['keyword']}%";
        }

        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'user_profile')
            ->andWhere('mobile LIKE :mobile')
            ->andWhere('truename LIKE :truename')
            ->andWhere('idcard LIKE :idcard')
            ->andWhere('id IN (:ids)')
            ->andWhere('mobile = :tel')
            ->andWhere('mobile <> :mobileNotEqual')
            ->andWhere('qq LIKE :qq');
    }

    public function declares()
    {
        return array(
        );
    }
}
