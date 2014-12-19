<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;

class SignController extends BaseController
{
    public function signAction(Request $request, $groupId)
    {   
        $user=$this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        if(!$this->getGroupMemberRole($groupId)){
            $this->getGroupService()->joinGroup($user,$groupId);
        }
        
        $userId=$user['id'];
        $sign=$this->getSignService()->userSign($userId, 'group_sign', $groupId);

        if(!$sign) return $this->createJsonResponse('false');
        return $this->createJsonResponse('success');
    }

    public function signRepairAction(Request $request, $groupId)
    {   
        $user=$this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        if(!$this->getGroupMemberRole($groupId)){
            $this->getGroupService()->joinGroup($user,$groupId);
        }

        $date=$request->query->get('day');
        
        $userId=$user['id'];
        $this->getSignService()->repairSign($userId, 'group_sign', $groupId,$date);
        
        return $this->createJsonResponse('success');
    }

    public function getSignedRecordsByPeriodAction(Request $request, $groupId)
    {   
        $user=$this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $userId=$user['id'];

        $startDay = $request->query->get('startDay');
        $endDay = $request->query->get('endDay');
    
        $userSigns = $this->getSignService()->getSignRecordsByPeriod($userId, 'group_sign', $groupId, $startDay, $endDay);
        $result = array();
        $result['records'] = array();
        if($userSigns) {
            foreach ($userSigns as $userSign) {
            $result['records'][] = array(
                'day' => date('d',$userSign['createdTime']),
                'time' => date('G点m分',$userSign['createdTime']),
                'rank' => $userSign['rank']);
            }
        }
        $userSignStatistics = $this->getSignService()->getSignUserStatistics($userId, 'group_sign', $groupId);
        $classSignStatistics = $this->getSignService()->getSignTargetStatistics('group_sign', $groupId, date('Ymd', time()));

        $result['todayRank'] = $this->getSignService()->getTodayRank($userId, 'group_sign', $groupId);
        $result['signedNum'] = $classSignStatistics['signedNum'];
        $result['keepDays'] = $userSignStatistics['keepDays'];
        
        return $this->createJsonResponse($result);
    }

    private function getSignService()
    {
        return $this->getServiceKernel()->createService('Custom:Sign.SignService');
    }

    private function getGroupMemberRole($userId)
    {
       $user = $this->getCurrentUser();

       if (!$user['id']) return 0;

       if ($this->getGroupService()->isOwner($userId, $user['id'])) return 2;

       if ($this->getGroupService()->isAdmin($userId, $user['id'])) return 3;

       if ($this->getGroupService()->isMember($userId, $user['id'])) return 1;

       return 0;
    }

    private function getGroupService() 
    {
        return $this->getServiceKernel()->createService('Group.GroupService');
    }
}