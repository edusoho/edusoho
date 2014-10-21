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
        $sql="UPDATE {$this->table} set {$fieldName} =null ";
        return $this->getConnection()->exec($sql);
    }
}