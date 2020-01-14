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
        $memberCourses = $this->getCourseMemberService()->findStudentMemberByUserId($this->getCurrentUser()->getId());

        if (empty($memberCourses)) {
            return array();
        }

        $courseIds = ArrayToolkit::column($memberCourses, 'courseId');
        $classroomIds = ArrayToolkit::column($memberCourses, 'classroomId');

        $conditions = array(
            'type' => 'live',
            'courseIds' => $courseIds,
            'status' => 'published',
            'startTime_GE' => $request->query->get('startTime', strtotime(date('00:00:00', time()))),
            'startTime_LE' => $request->query->get('endTime', strtotime(date('23:59:59', time()))),
        );

        $total = $this->getTaskService()->countTasks($conditions);

        if (empty($total)) {
            return array();
        }

        $lives = $this->getTaskService()->searchTasks($conditions, array('startTime' => 'DESC'), 0, $total);

        return $this->sortAndFilterDayLives($lives, $courseIds, $classroomIds);
    }

    protected function sortAndFilterDayLives($lives, $courseIds, $classroomIds)
    {
        $classroomCourses = array_combine($courseIds, $classroomIds);

        $activities = $this->getActivityService()->findActivities(ArrayToolkit::column($lives, 'activityId'), true, 0);
        $activities = ArrayToolkit::index($activities, 'id');

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        $dayLives = array();
        foreach ($lives as $live) {
            $live['activity'] = empty($activities[$live['activityId']]) ? null : $activities[$live['activityId']];

            $live['course'] = empty($courses[$live['courseId']]) ? null : $courses[$live['courseId']];

            $live['classroom'] = empty($classroomCourses[$live['courseId']]) || empty($classrooms[$classroomCourses[$live['courseId']]]) ? null : $classrooms[$classroomCourses[$live['courseId']]];

            $status = $live['startTime'] > time() ? 'created' : ($live['endTime'] < time() ? 'finished' : 'doing');

            $dayLives[$status][] = $live;
        }

        $filteredLives = array();

        array_filter(array('doing', 'created', 'finished'), function ($status) use ($dayLives, &$filteredLives) {
            if (empty($dayLives[$status])) {
                return false;
            }
            $filteredLives = array_merge($filteredLives, array_values($dayLives[$status]));
        });

        return array_values($filteredLives);
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
