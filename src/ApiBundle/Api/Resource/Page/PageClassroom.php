<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Resource\AbstractResource;

class PageClassroom extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $portal, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        if (empty($classroom)) {
            throw new NotFoundHttpException('班级不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }
        $member = $this->getClassroomService()->getClassroomMember($classroomId, $this->getCurrentUser()->getId());
        $isMemberNonExpired = empty($member) ? false : $this->getClassroomService()->isMemberNonExpired($classroom, $member);
        $classroom['member'] = $isMemberNonExpired ? $member : null;
        $this->getOCUtil()->single($classroom, array('creator', 'teacherIds', 'assistantIds', 'headTeacherId'));

        if (!empty($classroom['headTeacher'])) {
            $this->mergeProfile($classroom['headTeacher']);
        }

        $classroom['access'] = $this->getClassroomService()->canJoinClassroom($classroomId);
        $classroom['courses'] = $this->getClassroomService()->findCoursesByClassroomId($classroomId);

        $this->getOCUtil()->multiple($classroom['courses'], array('courseSetId'), 'courseSet');
        $this->getOCUtil()->multiple($classroom['courses'], array('creator', 'teacherIds'));

        $classroom['reviews'] = $this->getClassroomReviewService()->searchReviews(array('classroomId' => $classroomId, 'parentId' => 0), array('createdTime' => 'DESC'), 0, 5);
        foreach ($classroom['reviews'] as &$review) {
            $reviewPosts = $this->getClassroomReviewService()->searchReviews(array('parentId' => $review['id']), array('createdTime' => 'ASC'), 0, 5);
            $this->getOCUtil()->multiple($reviewPosts, array('userId'));
            $review['posts'] = $reviewPosts;
        }
        $this->getOCUtil()->multiple($classroom['reviews'], array('userId'));

        if ($this->isPluginInstalled('vip') && $classroom['vipLevelId'] > 0) {
            $apiRequest = new ApiRequest('/api/plugins/vip/vip_levels/'.$classroom['vipLevelId'], 'GET', array());
            $classroom['freeVipLevel'] = $this->invokeResource($apiRequest);
        }

        return $classroom;
    }

    private function mergeProfile(&$user)
    {
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($profile, $user);
    }

    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    private function getClassroomReviewService()
    {
        return $this->service('Classroom:ClassroomReviewService');
    }
}
