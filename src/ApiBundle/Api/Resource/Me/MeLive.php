<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskService;

class MeLive extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $courseMembers = $this->getCourseMemberService()->findStudentMemberByUserId($this->getCurrentUser()->getId());

        $courseIds = $this->getCanLearnCourseIds($courseMembers);

        if (empty($courseIds)) {
            return [];
        }

        $conditions = [
            'type' => 'live',
            'courseIds' => $courseIds,
            'status' => 'published',
            'startTime_GE' => $request->query->get('startTime'),
            'startTime_LE' => $request->query->get('endTime'),
        ];

        $total = $this->getTaskService()->countTasks($conditions);

        if (empty($total)) {
            return [];
        }

        $liveTasks = $this->getTaskService()->searchTasks($conditions, ['startTime' => 'ASC'], 0, $total);

        return $this->sortAndFilterLiveTasks($liveTasks, $courseMembers);
    }

    protected function getCanLearnCourseIds($courseMembers)
    {
        if (empty($courseMembers)) {
            return [];
        }
        $courseIds = array_column($courseMembers, 'courseId');
        $courseIds = $this->getCourseService()->searchCourses(['canLearn' => 1, 'courseIds' => $courseIds], [], 0, count($courseIds), 'id');

        return array_column($courseIds, 'id');
    }

    protected function sortAndFilterLiveTasks($liveTasks, $courseMembers)
    {
        $courseMembers = ArrayToolkit::index($courseMembers, 'courseId');

        $activities = $this->getActivityService()->findActivities(ArrayToolkit::column($liveTasks, 'activityId'), true, 0);
        $activities = ArrayToolkit::index($activities, 'id');

        $courseIds = ArrayToolkit::column($courseMembers, 'courseId');
        $classroomIds = ArrayToolkit::column($courseMembers, 'classroomId');

        $courses = $this->getCourseService()->searchCourses(['ids' => $courseIds], [], 0, count($courseIds), ['id', 'title', 'courseSetTitle']);
        $courses = ArrayToolkit::index($courses, 'id');

        $classrooms = $this->getClassroomService()->searchClassrooms(['classroomIds' => $classroomIds], [], 0, count($classroomIds), ['id', 'title']);
        $classrooms = ArrayToolkit::index($classrooms, 'id');

        $doingLives = [];
        $finishedLives = [];
        $notStartLives = [];
        foreach ($liveTasks as $live) {
            $live['activity'] = empty($activities[$live['activityId']]) ? null : $activities[$live['activityId']];

            $live['course'] = empty($courses[$live['courseId']]) ? null : $courses[$live['courseId']];

            $live['classroom'] = empty($courseMembers[$live['courseId']]['classroomId']) || empty($classrooms[$courseMembers[$live['courseId']]['classroomId']]) ? null : $classrooms[$courseMembers[$live['courseId']]['classroomId']];

            $live['startTime'] > time() ? $notStartLives[] = $live : ($live['endTime'] < time() ? $finishedLives[] = $live : $doingLives[] = $live);
        }

        return array_merge($doingLives, $notStartLives, $finishedLives);
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }
}
