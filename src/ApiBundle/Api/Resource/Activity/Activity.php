<?php

namespace ApiBundle\Api\Resource\Activity;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Activity extends AbstractResource
{
    /**
    * @ApiConf(isRequiredAuth=false)
    */
    public function get(ApiRequest $request, $activityId)
    {
       $activity = $this->getActivityService()->getActivity($activityId, true);
       if (!empty($activity)) {
            $this->getCourseService()->tryManageCourse($activity['fromCourseId']);
            return  $activity;
       }
       
       $user = $this->getCurrentUser();
       return $this->getCourseMemberService()->isCourseStudent($activity['fromCourseId'], $user['id']) ? $activity : array();
    }

    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
