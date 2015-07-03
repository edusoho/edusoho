<?php
namespace Topxia\Service\User\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\User\UserCommonAdminService;
use Topxia\Common\ArrayToolkit;

class UserCommonAdminServiceImpl extends BaseService implements UserCommonAdminService
{   
    public function getCommonAdmin($id)
    {
        return $this->getCommonAdminDao()->getCommonAdmin($id);
    }

    public function findCommonAdminByUserId($userId)
    {   
        if(!$userId) {

            return null;
        }

        $admins = $this->getCommonAdminDao()->findCommonAdminByUserId($userId);

        return $admins;
    }

    public function getCommonAdminByUserIdAndUrl($userId, $url)
    {   
        if(!$userId) {

            return null;
        }

        $admin = $this->getCommonAdminDao()->getCommonAdminByUserIdAndUrl($userId, $url);

        return $admin;
    }

    public function addCommonAdmin($admin)
    {
        if (!isset($admin['userId']) || empty($admin['userId'])) {

            throw $this->createServiceException("userId不能为空！");
        }

        if (!isset($admin['title']) || empty($admin['title'])) {

            throw $this->createServiceException("名称不能为空！");
        }

        if (!isset($admin['url']) || empty($admin['url'])) {
            
            throw $this->createServiceException("url不能为空！");
        }

        $admin['createdTime'] = time();

        $admin = $this->getCommonAdminDao()->addCommonAdmin($admin);

        return $admin;
    }

    public function deleteCommonAdmin($id)
    {   
        $user = $this->getCurrentUser();

        $admin =$this->getCommonAdmin($id);

        if ($user['id'] == $admin['userId']) {

            $this->getCommonAdminDao()->deleteCommonAdmin($id);
        }
    }

    protected function getCommonAdminDao() 
    {
        return $this->createDao('User.UserCommonAdminDao');
    }
}