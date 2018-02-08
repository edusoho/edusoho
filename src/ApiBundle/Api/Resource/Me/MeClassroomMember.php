<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use ApiBundle\Api\Annotation\ResponseFilter;

class MeClassroomMember extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Classroom\ClassroomMemberFilter", mode="simple")
     */
    public function get(ApiRequest $request, $classroomId)
    {
        $member = $this->getClassroomService()->getClassroomMember($classroomId, $this->getCurrentUser()->getId());

        if ($member) {
            $member['access'] = $this->getClassroomService()->canLearnClassroom($classroomId);
        }

        return $member;
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }
}
