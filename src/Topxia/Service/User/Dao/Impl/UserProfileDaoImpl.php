<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserProfileDao;

class UserProfileDaoImpl extends BaseDao implements UserProfileDao
{
    protected $table = 'user_profile';

    public function getProfile($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

	public function addProfile($profile)
	{
        $affected = $this->getConnection()->insert($this->table, $profile);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert profile error.');
        }
        return $this->getProfile($this->getConnection()->lastInsertId());
	}

	public function updateProfile($id, $profile)
	{
        $this->getConnection()->update($this->table, $profile, array('id' => $id));
        return $this->getProfile($id);
	}

    public function findProfilesByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
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

        $sql="UPDATE {$this->table} set {$fieldName} =null ";
        return $this->getConnection()->exec($sql);
    }
}