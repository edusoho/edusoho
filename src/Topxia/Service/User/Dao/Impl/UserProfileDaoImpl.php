<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserProfileDao;

class UserProfileDaoImpl extends BaseDao implements UserProfileDao
{
    protected $table = 'user_profile';

    public function getProfile($id)
    {
        return $this->fetch($id);
    }

	public function addProfile($profile)
	{
		$id = $this->insert($profile);
        return $this->getProfile($id);
	}

	public function updateProfile($id, $profile)
	{
		return $this->update($id, $profile);
	}

    public function findProfilesByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }
}