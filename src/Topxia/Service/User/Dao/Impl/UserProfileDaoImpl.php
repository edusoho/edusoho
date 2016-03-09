<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserProfileDao;

class UserProfileDaoImpl extends BaseDao implements UserProfileDao
{
    protected $table = 'user_profile';

    public function getProfile($id)
    {
        $that = $this;
        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        );
    }

    public function addProfile($profile)
    {
        $affected = $this->getConnection()->insert($this->table, $profile);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert profile error.');
        }

        return $this->getProfile($this->getConnection()->lastInsertId());
    }

    public function updateProfile($id, $profile)
    {
        $this->getConnection()->update($this->table, $profile, array('id' => $id));
        $this->clearCached();
        return $this->getProfile($id);
    }

    public function findProfilesByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';

        $that = $this;
        $keys = implode(',', $ids);
        return $this->fetchCached("ids:{$keys}", $marks, $ids, function ($marks, $ids) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id IN ({$marks});";
            return $that->getConnection()->fetchAll($sql, $ids);
        }

        );
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
        $result = $this->getConnection()->exec($sql);
        $this->clearCached();
        return $result;
    }

    public function searchProfiles($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createProfileQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchProfileCount($conditions)
    {
        $builder = $this->createProfileQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
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
            ->andWhere('qq LIKE :qq');
    }
}
