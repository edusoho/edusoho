<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskService;

class MeCourse extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $conditions['status'] = 'published';
        $conditions['classroomId'] = 0;
        $conditions['joinedType'] = 'course';
        $conditions['userId'] = $this->getCurrentUser()->getId();

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $members = $this->getCourseMemberService()->searchMembers(
            $conditions,
            array('lastViewTime' => 'DESC'),
            $offset,
            $limit
        );

        $courses = $this->getCourseService()->findCoursesByIds(array_column($members, 'courseId'));

        $courses = $this->appendAttrAndOrder($courses, $members);

        $total = $this->getCourseMemberService()->countMembers($conditions);

        $this->getOCUtil()->multiple($courses, array('courseSetId'), 'courseSet');

        return $this->makePagingObject($courses, $total, $offset, $limit);
    }

    private function appendAttrAndOrder($courses, $members)
    {
        $orderedCourses = array();
        foreach ($members as $member) {
            $courseId = $member['courseId'];
            if (!empty($courses[$courseId])) {
                $courses[$courseId]['learnedNum'] = $member['learnedNum'];
                $courses[$courseId]['publishedTaskNum'] = $this->getTaskService()->countTasks(array(
                    'status' => 'published',
                    'courseId' => $courseId
                ));
                $orderedCourses[] = $courses[$member['courseId']];
            }
        }

        return $orderedCourses;
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->service('Task:TaskService');
    }
}