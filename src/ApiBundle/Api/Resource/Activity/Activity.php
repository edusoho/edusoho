<?php

namespace ApiBundle\Api\Resource\Activity;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;

class Activity extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId, true);
        if (empty($activity)) {
            return array();
        }

        $user = $this->getCurrentUser();
        if ($this->getCourseMemberService()->isCourseStudent($activity['fromCourseId'], $user['id'])) {
            return $activity;
        }

        $this->getCourseService()->tryManageCourse($activity['fromCourseId']);

        return  $activity;
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
