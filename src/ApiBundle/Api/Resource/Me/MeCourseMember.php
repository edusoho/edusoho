<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\MemberException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use ApiBundle\Api\Annotation\ResponseFilter;

class MeCourseMember extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Course\CourseMemberFilter", mode="public"))
     */
    public function get(ApiRequest $request, $courseId)
    {
        $courseMember = $this->getCourseMemberService()->getCourseMember($courseId, $this->getCurrentUser()->getId());
        $this->getOCUtil()->single($courseMember, array('userId'));

        if ($courseMember) {
            $courseMember['access'] = $this->getCourseService()->canLearnCourse($courseId);
        }

        return $courseMember;
    }

    public function remove(ApiRequest $request, $courseId)
    {
        $reason = $request->request->get('reason', '从App退出课程');

        $user = $this->getCurrentUser();

        $this->getCourseService()->tryTakeCourse($courseId);

        $member = $this->getCourseMemberService()->getCourseMember($courseId, $user->getId());

        if (empty($member)) {
            throw MemberException::NOTFOUND_MEMBER();
        }

        $this->getCourseMemberService()->removeStudent($courseId, $user->getId(), array(
           'reason' => $reason,
        ));

        return array('success' => true);
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
