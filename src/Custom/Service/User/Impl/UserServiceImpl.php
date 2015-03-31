<?php
namespace Custom\Service\User\Impl;

use Topxia\Service\User\Impl\UserServiceImpl as BaseService;
use Custom\Service\User\UserService;

class UserServiceImpl extends BaseService implements UserService
{
	
	public function updateUser($id,$field){
		
		$this->getUserDao()->updateUser($id,$field);
	}


	 public function searchUsers(array $conditions, array $orderBy, $start, $limit)
    {
        $users = $this->getCustomUserDao()->searchUsers($conditions, $orderBy, $start, $limit);
        return UserSerialize::unserializes($users);
    }

    public function searchUserCount(array $conditions)
    {
        return $this->getCustomUserDao()->searchUserCount($conditions);
    }
	
    private function getUserDao()
    {
        return $this->createDao('User.UserDao');
    }

    private function getCustomUserDao()
    {
        return $this->createDao('Custom:User.CustomUserDao');
    }
	
}

class UserSerialize
{
    public static function serialize(array $user)
    {
        $user['roles'] = empty($user['roles']) ? '' :  '|' . implode('|', $user['roles']) . '|';
        return $user;
    }

    public static function unserialize(array $user = null)
    {
        if (empty($user)) {
            return null;
        }
        $user['roles'] = empty($user['roles']) ? array() : explode('|', trim($user['roles'], '|')) ;
        return $user;
    }

    public static function unserializes(array $users)
    {
        return array_map(function($user) {
            return UserSerialize::unserialize($user);
        }, $users);
    }
}