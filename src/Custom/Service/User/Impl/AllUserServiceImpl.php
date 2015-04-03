<?php
namespace Custom\Service\User\Impl;

use Custom\Service\User\AllUserService;
use Topxia\Service\Cash\Impl\CashServiceImpl as TopxiaCashServiceImpl;

class AllUserServiceImpl extends  TopxiaCashServiceImpl implements AllUserService 
{
    public function AllUser()
    {
        return  $this->getAllUserDao()->getAllUser();
    }
    protected function getAllUserDao()
    {
        return $this->createDao('Custom:User.AllUserDao');
    }

}