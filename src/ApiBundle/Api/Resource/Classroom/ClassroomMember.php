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
        $this->getClassroomService()->tryFreeJoin($classroom['id']);

        return $this->getClassroomService()->getClassroomMember($classroom['id'], $this->getCurrentUser()->getId());
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }
}