<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Controller\BuyFlowController;
use Biz\Classroom\Service\ClassroomService;

class ClassroomBuyController extends BuyFlowController
{
    protected $targetType = 'classroom';

    protected function needOpenPayment($id)
    {
        $payment = $this->getSettingService()->get('payment');
        $classroom = $this->getClassroomService()->getClassroom($id);
        $vipJoinEnabled = false;
        if ($this->isPluginInstalled('Vip') && $this->setting('vip.enabled')) {
            $user = $this->getCurrentUser();
            $vipJoinEnabled = 'ok' === $this->getVipService()->checkUserInMemberLevel($user['id'], $classroom['vipLevelId']);
        }

        return $classroom['price'] > 0 && !$payment['enabled'] && !$vipJoinEnabled;
    }

    protected function tryFreeJoin($id)
    {
        $this->getClassroomService()->tryFreeJoin($id);
    }

    protected function getSuccessUrl($id)
    {
        return $this->generateUrl('classroom_courses', array('classroomId' => $id));
    }

    protected function isJoined($id)
    {
        $user = $this->getUser();

        return $this->getClassroomService()->isClassroomStudent($id, $user['id']);
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }
}
