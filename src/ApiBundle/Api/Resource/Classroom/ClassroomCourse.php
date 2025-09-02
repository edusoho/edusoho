<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskService;

class ClassroomCourse extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     * @ResponseFilter(class="ApiBundle\Api\Resource\Course\CourseFilter")
     */
    public function search(ApiRequest $request, $classroomId)
    {
        $title = trim($request->query->get('title', ''));
        if ($title) {
            $courses = $this->getClassroomService()->findSortedCoursesByClassroomIdAndCourseSetTitle($classroomId, $title);
        } else {
            $courses = $this->getClassroomService()->findCoursesByClassroomId($classroomId);
        }
        if ($this->getCurrentUser()->isLogin()) {
            $courses = $this->appendLastLearnTask($courses);
        }

        $this->getOCUtil()->multiple($courses, ['courseSetId'], 'courseSet');
        $this->getOCUtil()->multiple($courses, ['creator', 'teacherIds']);
        foreach ($courses as &$course) {
            $course['videoMaxLevel'] = $this->getCourseService()->getVideoMaxLevel($course['id']);
        }

        return $courses;
    }

    private function appendLastLearnTask($courses)
    {
        $courseMembers = $this->getCourseMemberService()->findCourseMembersByUserIdAndCourseIds($this->getCurrentUser()->getId(), array_column($courses, 'id'));
        $tasks = $this->getTaskService()->findTasksByIds(array_column($courseMembers, 'lastLearnTaskId'));
        $courseMembers = array_column($courseMembers, null, 'courseId');
        $tasks = array_column($tasks, null, 'id');
        foreach ($courses as &$course) {
            $member = $courseMembers[$course['id']] ?? [];
            $course['lastLearnTask'] = empty($tasks[$member['lastLearnTaskId']]) ? null : [
                'id' => $member['lastLearnTaskId'],
                'number' => $tasks[$member['lastLearnTaskId']]['number'],
                'title' => $tasks[$member['lastLearnTaskId']]['title'],
            ];
        }

        return $courses;
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
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

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->service('Task:TaskService');
    }
}
