<?php
namespace Custom\Service\User\Impl;

use Topxia\Service\User\Impl\UserServiceImpl as BaseService;
use Custom\Service\User\UserService;

class UserServiceImpl extends BaseService implements UserService
{
	
	public function updateUser($id,$field){
		
		$this->getUserDao()->updateUser($id,$field);
	}
	
    private function getUserDao()
    {
        return $this->createDao('User.UserDao');
    }
	
}