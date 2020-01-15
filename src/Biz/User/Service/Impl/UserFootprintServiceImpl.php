<?php

namespace Biz\User\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Biz\User\Dao\UserFootprintDao;
use Biz\User\Service\UserFootprintService;

class UserFootprintServiceImpl extends BaseService implements UserFootprintService
{
    public function createUserFootprint($footprint)
    {
        $footprint = $this->checkAndFilterFootprint($footprint);

        $footprint['date'] = empty($footprint['date']) ? strtotime(date('00:00:00', time())) : strtotime(date('00:00:00', $footprint['date']));

        $conditions = $footprint;
        $existedFootprint = $this->searchUserFootprints($conditions, array(), 0, 1);

        if (empty($existedFootprint)) {
            return $this->getUserFootprintDao()->create($footprint);
        }

        return $this->updateFootprint($existedFootprint[0]['id'], $footprint);
    }

    public function updateFootprint($id, $footprint)
    {
        $footprint = $this->checkAndFilterFootprint($footprint);

        return $this->getUserFootprintDao()->update($id, $footprint);
    }

    public function searchUserFootprints(array $conditions, array $order, $start, $limit, $columns = array())
    {
        if (empty($conditions)) {
            return array();
        }

        return $this->getUserFootprintDao()->search($conditions, $order, $start, $limit, $columns);
    }

    public function countUserFootprints($conditions)
    {
        return $this->getUserFootprintDao()->count($conditions);
    }

    public function prepareUserFootprintsByType($footprints, $type)
    {
        if (empty($footprints)) {
            return array();
        }

        $method = 'prepare'.ucfirst($type).'Footprints';

        if (method_exists($this, $method)) {
            return $this->$method($footprints);
        }

        return $footprints;
    }

    public function deleteUserFootprintsBeforeDate($date)
    {
        return $this->getUserFootprintDao()->deleteBeforeDate($date);
    }

    protected function prepareTaskFootprints($footprints)
    {
        $taskIds = ArrayToolkit::column($footprints, 'targetId');

        $tasks = $this->getTaskService()->findTasksByIds($taskIds);
        if (empty($tasks)) {
            return $footprints;
        }

        $tasks = ArrayToolkit::index($tasks, 'id');

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($tasks, 'courseId'));
        $courses = ArrayToolkit::index($courses, 'id');

        $classroomCourses = $this->getClassroomService()->findClassroomsByCoursesIds(ArrayToolkit::column($courses, 'id'));
        $classroomCourses = ArrayToolkit::index($classroomCourses, 'courseId');

        $classrooms = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($classroomCourses, 'classroomId'));

        foreach ($footprints as &$footprint) {
            $task = empty($tasks[$footprint['targetId']]) ? null : $tasks[$footprint['targetId']];
            if (empty($task)) {
                continue;
            }

            $course = empty($courses[$task['courseId']]) ? null : $courses[$task['courseId']];
            $classroom = empty($classroomCourses[$task['courseId']]['classroomId']) || empty($classrooms[$classroomCourses[$task['courseId']]['classroomId']]) ? null : $classrooms[$classroomCourses[$task['courseId']]['classroomId']];

            if ($task['isLesson']) {
                $task['task'] = $task;
                $task['course'] = $course;
                $task['classroom'] = $classroom;
                $footprint['target'] = $task;
            } else {
                $lesson = $this->getTaskService()->searchTasks(array('categoryId' => $task['categoryId'], 'isLesson' => 1), array(), 0, 1);
                $lesson = array_pop($lesson);
                $lesson['task'] = $task;
                $lesson['course'] = $course;
                $lesson['classroom'] = $classroom;

                $footprint['target'] = $lesson;
            }
        }

        return $footprints;
    }

    protected function checkAndFilterFootprint($footprint)
    {
        if (!ArrayToolkit::requireds($footprint, array('targetType', 'targetId', 'event'))) {
            throw $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        return array(
            'userId' => empty($footprint['userId']) ? $this->getCurrentUser()->getId() : $footprint['userId'],
            'targetType' => $footprint['targetType'],
            'targetId' => $footprint['targetId'],
            'event' => $footprint['event'],
            'date' => empty($footprint['date']) ? time() : $footprint['date'],
        );
    }

    /**
     * @return UserFootprintDao
     */
    protected function getUserFootprintDao()
    {
        return $this->createDao('User:UserFootprintDao');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
