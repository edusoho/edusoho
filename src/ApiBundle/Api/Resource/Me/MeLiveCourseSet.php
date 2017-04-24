<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use ApiBundle\Api\Annotation\ApiFilter;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;

class MeLiveCourseSet extends AbstractResource
{
    /**
     * @ApiFilter(class="ApiBundle\Api\Resource\CourseSet\CourseSetFilter", mode="simple")
     */
    public function search(ApiRequest $request)
    {
        $allLiveCourseSets = $this->getCourseSetService()->searchCourseSets(
            array('status' => 'published', 'type' => 'live'),
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $members = $this->getCourseMemberService()->searchMembers(
            array('courseSetIds' => array_column($allLiveCourseSets, 'id')),
            array('lastLearnTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );
        return array_values($this->getCourseSetService()->findCourseSetsByIds($this->getCourseSetIds($members)));
    }

    private function getCourseSetIds($members)
    {
        return array_values(array_unique(array_column($members, 'courseSetId')));
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }
}