<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        if (!$member) {
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
        if ($classroom['isFree'] == 1 || $classroom['price'] == 0) {
            $member = $this->getMemberService()->becomeStudent($classroom['id'], $this->getCurrentUser()->id);

            $classroomSet = $this->getCourseSetService()->getCourseSet($classroom['courseSetId']);

            $systemOrder = array(
                'userId' => $this->getCurrentUser()->id,
                'title' => "购买课程《{$classroomSet['title']}》- {$classroom['title']}",
                'targetType' => OrderService::TARGETTYPE_COURSE,
                'targetId' => $classroom['id'],
                'amount' => 0,
                'totalPrice' => $classroom['price'],
                'snPrefix' => OrderService::SNPREFIX_C,
                'payment' => '',
            );

            $order = $this->getOrderService()->createSystemOrder($systemOrder);
            $this->getMemberService()->updateMember($member['id'], array(
                'orderId' => $order['id']
            ));

            return $member;
        } else {
            return null;
        }
    }

    private function vipJoin($classroom)
    {
        if (!$this->isPluginInstalled('vip')) {
            return null;
        }

        list($success, $message) = $this->service('VipPlugin:Vip:VipFacadeService')->joinCourse($classroom['id']);
        if ($success) {
            return $this->getMemberService()->getCourseMember($classroom['id'], $this->getCurrentUser()->getId());
        } else {
            return null;
        }
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }
}