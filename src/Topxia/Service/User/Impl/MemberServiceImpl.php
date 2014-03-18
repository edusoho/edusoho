<?php
namespace Topxia\Service\User\Impl;
use Topxia\Service\User\MemberService;
use Topxia\Service\Common\BaseService;
class MemberServiceImpl extends BaseService implements MemberService
{
    public function checkMemberName($MemberName)
    {
        // 1 user  is exits? 2 is member ?  
        $avaliable = $this->getUserService()->isNicknameAvaliable($MemberName);
        if (!$avaliable) {
            $isMember = $this->isMemberNameAvaliable($MemberName);
            if($isMember){
               return array('error_duplicate','该用户已经是会员！');
            }
            return array('success','');
        }
        return array('error_duplicate','用户名不存在，请检查！');
    }
    
    public function isMemberNameAvaliable($MemberName)
    {
        if(!$MemberName){
            return false;
        }
        return $this->getUserService()->findMemberByNickname($MemberName);
    }
    
    public function updateMemberLevel($Userdata)
    {
        return $this->getUserService()->updateMemberLevel($Userdata);
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }

}

