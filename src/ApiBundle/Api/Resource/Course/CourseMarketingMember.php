<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseException;
use Biz\Marketing\Service\MarketingService;
use ApiBundle\Api\Annotation\ResponseFilter;

class CourseMarketingMember extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Course\CourseMemberFilter", mode="public")
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function get(ApiRequest $request, $courseId, $phoneNumber)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            throw CourseException::NOTFOUND_COURSE();
        }

        $user = $this->getUserService()->getUserByVerifiedMobile($phoneNumber);
        if (empty($user)) {
            return null;
        }

        $courseMember = $this->getCourseMemberService()->getCourseMember($courseId, $user['id']);
        $this->getOCUtil()->single($courseMember, array('userId'));

        return $courseMember;
    }

    /**
     * @return MarketingService
     */
    protected function getMarketingService()
    {
        return $this->service('Marketing:MarketingService');
    }

    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
