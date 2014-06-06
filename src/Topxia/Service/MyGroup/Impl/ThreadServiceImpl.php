<?php

namespace Topxia\Service\MyGroup\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\MyGroup\MyGroupService;
use Topxia\Service\MyGroup\ThreadService;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\File\File;

class ThreadServiceImpl extends BaseService implements ThreadService {

//添加小组
    public function publishthread($info) {
//获得用户信息
        $user = $this->getCurrentUser();
        if (empty($info['title'])) {
            throw $this->createServiceException("标题名称为空！");
        }
//执行插入
        $status=$this->getThreadDao()->addThread($info);
        if($status){
            $this->getMyGroupDao()->updatethreadNum($info['groupId'],'+');
            $this->getGroupMemberDao()->updatethreadNum($info['groupId'],$info['memberId'],'+');
        }
        return $status;
    }
    public function searchThread($id,$strat,$limit,$sort){
        return $this->getThreadDao()->searchThread($id,$strat,$limit,$sort);
    }
    private function getLogService() {
        return $this->createService('System.LogService');
    }

    private function getMyGroupDao() {
        return $this->createDao('MyGroup.MyGroupDao');
    }
    private function getGroupMemberDao() {
        return $this->createDao('MyGroup.GroupMemberDao');
    }
    private function getUserDao() {
        return $this->createDao('User.UserDao');
    }
     private function getThreadDao()
    {
        return $this->createDao('MyGroup.ThreadDao');
    }

}
