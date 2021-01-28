<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ResponseFilter;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;

class MeLiveCourseSet extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\CourseSet\CourseSetFilter", mode="simple")
     */
    public function search(ApiRequest $request)
    {
        $allLiveCourseSets = $this->getCourseSetService()->searchCourseSets(
            array('status' => 'published', 'type' => 'live', 'parentId' => 0),
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        if (!$allLiveCourseSets) {
            return array();
        }

        $members = $this->getCourseMemberService()->searchMembers(
            array('courseSetIds' => array_column($allLiveCourseSets, 'id'), 'userId' => $this->getCurrentUser()->getId()),
            array('lastLearnTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $uniqueMemberIds = $this->getUniqueCourseSetIds($members);
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($uniqueMemberIds);

        return array_values($this->orderByLastViewTime($courseSets, $uniqueMemberIds));
    }

    private function getUniqueCourseSetIds($members)
    {
        return array_values(array_unique(array_column($members, 'courseSetId')));
    }

    private function orderByLastViewTime($courseSets, $uniqueCourseSetIds)
    {
        $orderedCourseSets = array();
        foreach ($uniqueCourseSetIds as $courseSetId) {
            if (!empty($courseSets[$courseSetId])) {
                $orderedCourseSets[] = $courseSets[$courseSetId];
            }
        }

        return $orderedCourseSets;
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
