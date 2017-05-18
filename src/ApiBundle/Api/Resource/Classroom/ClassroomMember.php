<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomOrderService;
use Biz\Classroom\Service\ClassroomService;
use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use VipPlugin\Biz\Vip\Service\VipFacadeService;

class ClassroomMember extends AbstractResource
{
    public function add(ApiRequest $request, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        if (!$classroom) {
            throw new NotFoundHttpException('classroom.not_found', null, ErrorCode::RESOURCE_NOT_FOUND);
        }

        $access = $this->getClassroomService()->canJoinClassroom($classroomId);

        if ($access['code'] != 'success') {
            throw new BadRequestHttpException($access['msg']);
        }

        $member = $this->getClassroomService()->getClassroomMember($classroomId, $this->getCurrentUser()->getId());
        if (!$member || $member['role'] == array('auditor')) {

            $member = $this->tryJoin($classroom);
        }

        if ($member) {
            $this->getOCUtil()->single($member, array('userId'));
            return $member;
        }

        return null;
    }

    private function tryJoin($classroom)
    {
        $member = $this->freeJoin($classroom);
        if ($member) {
            return $member;
        }

        return $this->vipJoin($classroom);
    }

    private function freeJoin($classroom)
    {
        if ($classroom['price'] == 0) {
            $orderInfo = array();
            $orderInfo['userId'] = $this->getCurrentUser()->getId();
            $orderInfo['targetId'] = $classroom['id'];
            $orderInfo['targetType'] = 'classroom';
            $orderInfo['amount'] = 0;
            $orderInfo['totalPrice'] = 0;
            $coinSetting = $this->getSettingService()->get('coin');
            $orderInfo['priceType'] = empty($coinSetting['priceType']) ? 'RMB' : $coinSetting['priceType'];
            $orderInfo['coinRate'] = empty($coinSetting['coinRate']) ? 1 : $coinSetting['coinRate'];
            $orderInfo['coinAmount'] = 0;
            $orderInfo['payment'] = 'alipay';
            $this->getClassroomOrderService()->createOrder($orderInfo);

            return $this->getClassroomService()->getClassroomMember($classroom['id'], $this->getCurrentUser()->getId());
        } else {
            return null;
        }
    }

    private function vipJoin($classroom)
    {
        if (!$this->isPluginInstalled('vip')) {
            return null;
        }

        list($success, $message) = $this->getVipFacadeService()->joinClassroom($classroom['id']);
        if ($success) {
            return $this->getClassroomService()->getClassroomMember($classroom['id'], $this->getCurrentUser()->getId());
        } else {
            return null;
        }
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    /**
     * @return VipFacadeService
     */
    private function getVipFacadeService()
    {
        return $this->service('VipPlugin:Vip:VipFacadeService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return ClassroomOrderService
     */
    private function getClassroomOrderService()
    {
        return $this->service('Classroom:ClassroomOrderService');
    }
}