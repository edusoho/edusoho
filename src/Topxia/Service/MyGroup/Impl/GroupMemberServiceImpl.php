<?php

namespace Topxia\Service\MyGroup\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\MyGroup\GroupMemberService;

class GroupMemberServiceImpl extends BaseService implements GroupMemberService {

    public function joinGroup($id, $title) {
        $user = $this->getCurrentUser();
        if (empty($id)) {
            throw $this->createServiceException("加入小组失败！");
        }

        $joinid = $this->getGroupMemberDao()->joinGroup($id, $user['id']);
        if ($joinid) {
            $this->getMyGroupDao()->updatememberNum($id, '+');
            $this->getLogService()->info('groupjoin', 'create', "加入了小组({$title})");
        }
        return $joinid;
    }

    public function exitGroup($id, $title) {
        $user = $this->getCurrentUser();
        if (empty($id)) {
            throw $this->createServiceException("退出小组失败！");
        }

        $joinid = $this->getGroupMemberDao()->exitGroup($id, $user['id']);
        if ($joinid) {
            $this->getMyGroupDao()->updatememberNum($id, '-');
            $this->getLogService()->info('groupexit', 'create', "退出了小组({$title})");
        }

        return $joinid;
    }
     public function searchjoinGroup($condtion, $start, $limit, $sort){
         $group=$this->getGroupMemberDao()->searchjoinGroup($condtion, $start, $limit, $sort);
         $groupinfo=array();
         foreach ($group as $id) {
            $groupinfo[]=$this->getMyGroupDao()->getGroupinfo($id['groupId']); 
         }
         return $groupinfo;        
     }
     public function getgroupmember_recentlyinfo($id){
        $memberid=$this->getGroupMemberDao()->getgroupmember_recentlyinfo($id);
        $memberInfo=array();
        foreach ($memberid as $id) {

           $memberInfo[]=$this->getUserDao()->getUser($id['memberId']);
        }
       
        return $memberInfo;
     }
    public function getgroupmember_info($id){
        $memberid=$this->getGroupMemberDao()->getgroupmember_info($id);
        $memberInfo=array();
        foreach ($memberid as $id) {

           $memberInfo[]=$this->getUserDao()->getUser($id['memberId']);
        }
       
        return $memberInfo;
     }
    
    public function ismember($id, $userid) {
        return $this->getGroupMemberDao()->ismember($id, $userid);
    }

    private function getLogService() {
        return $this->createService('System.LogService');
    }

    private function getGroupMemberDao() {
        return $this->createDao('MyGroup.GroupMemberDao');
    }

    private function getMyGroupDao() {
        return $this->createDao('MyGroup.MyGroupDao');
    }
    private function getUserDao() {
        return $this->createDao('User.UserDao');
    }
}
