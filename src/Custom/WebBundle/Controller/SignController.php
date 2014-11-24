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

        $userId=$user['id'];
        $this->getSignService()->userSign($userId, 'group_sign', $groupId);
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

}