<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Controller\BuyFlowController;
use Biz\Classroom\Service\ClassroomService;
use VipPlugin\Biz\Marketing\VipRightSupplier\ClassroomVipRightSupplier;

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
            $vipJoinEnabled = 'ok' === $this->getVipService()->checkUserVipRight($user['id'], ClassroomVipRightSupplier::CODE, $classroom['id']);
        }

        return $classroom['price'] > 0 && !$payment['enabled'] && !$vipJoinEnabled;
    }

    protected function tryFreeJoin($id)
    {
        $this->getClassroomService()->tryFreeJoin($id);
    }

    protected function getSuccessUrl($id)
    {
        return $this->generateUrl('classroom_courses', ['classroomId' => $id]);
    }

    protected function isJoined($id)
    {
        $user = $this->getUser();

        return $this->getClassroomService()->isClassroomStudent($id, $user['id']);
    }

    protected function needInformationCollectionBeforeJoin($targetId)
    {
        $classroom = $this->getClassroomService()->getClassroom($targetId);
        if ($this->isPluginInstalled('Vip')) {
            $vipRight = $this->getVipRightService()->getVipRightBySupplierCodeAndUniqueCode(ClassroomVipRightSupplier::CODE, $classroom['id']);
            if ((0 != $classroom['price']) && empty($vipRight)) {
                return [];
            }
        }

        $event = $this->getInformationCollectEventService()->getEventByActionAndLocation('buy_before', ['targetType' => 'classroom', 'targetId' => $targetId]);
        if (empty($event)) {
            return [];
        }

        $url = $this->generateUrl('information_collect_event', [
            'eventId' => $event['id'],
            'goto' => $this->generateUrl('classroom_buy', ['id' => $targetId]),
        ]);

        return [$event['id'], 'url' => $url];
    }

    protected function needInformationCollectionAfterJoin($targetId)
    {
        $event = $this->getInformationCollectEventService()->getEventByActionAndLocation('buy_after', ['targetType' => 'classroom', 'targetId' => $targetId]);
        if (empty($event)) {
            return [];
        }

        $url = $this->generateUrl('information_collect_event', [
            'eventId' => $event['id'],
            'goto' => $this->getSuccessUrl($targetId),
        ]);

        return [$event['id'], 'url' => $url];
    }

    protected function getInformationCollectEventService()
    {
        return $this->createService('InformationCollect:EventService');
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
