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

    public function remove(ApiRequest $request, $classroomId)
    {
        $reason = $request->request->get('reason', '从移动端退出班级');

        $user = $this->getCurrentUser();

        $this->getClassroomService()->tryTakeClassroom($classroomId);

        $member = $this->getClassroomService()->getClassroomMember($classroomId, $user->getId());

        if (empty($member)) {
            throw MemberException::NOTFOUND_MEMBER();
        }

        $this->getClassroomService()->removeStudent($classroomId, $user->getId(), array(
           'reason' => $reason,
        ));

        return array('success' => true);
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }
}
