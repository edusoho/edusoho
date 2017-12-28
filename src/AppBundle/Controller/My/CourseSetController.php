<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\Paginator;
use Biz\Classroom\Service\ClassroomService;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Course\CourseBaseController;

class CourseSetController extends CourseBaseController
{
    public function favoriteAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countUserFavorites($user['id']),
            12
        );

        $courseFavorites = $this->getCourseSetService()->searchUserFavorites(
            $user['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render(
            'my/learning/course-set/favorite.html.twig',
            array(
                'courseFavorites' => $courseFavorites,
                'paginator' => $paginator,
            )
        );
    }

    public function teachingAction(Request $request, $filter = 'normal')
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $conditions = array(
            'type' => $filter,
            'parentId' => 0,
        );

        if ('classroom' == $filter) {
            $conditions['parentId_GT'] = 0;
            unset($conditions['type']);
            unset($conditions['parentId']);
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countUserTeachingCourseSets($user['id'], $conditions),
            20
        );

        $courseSets = $this->getCourseSetService()->searchUserTeachingCourseSets(
            $user['id'],
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $service = $this->getCourseService();
        $courseSets = array_map(
            function ($set) use ($user, $service) {
                $set['courseNum'] = $service->countCourses(array(
                    'courseSetId' => $set['id'],
                ));

                return $set;
            },
            $courseSets
        );

        $classrooms = array();

        if ('classroom' == $filter) {
            $classrooms = $this->getClassroomService()->findClassroomsByCourseSetIds(
                ArrayToolkit::column($courseSets, 'id')
            );
            $classrooms = ArrayToolkit::index($classrooms, 'courseSetId');

            foreach ($classrooms as &$classroom) {
                $_classroom = $this->getClassroomService()->getClassroom($classroom['classroomId']);
                $classroom['classroomTitle'] = $_classroom['title'];
            }
        }

        return $this->render(
            'my/teaching/course-sets.html.twig',
            array(
                'courseSets' => $courseSets,
                'classrooms' => $classrooms,
                'paginator' => $paginator,
                'filter' => $filter,
            )
        );
    }

    public function teachingLivesCalendarAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }
        
        $liveCourseNumber = $this->getTaskService()->getTodayLiveCourseNumber();
        $openLiveCourseNumber = $this->getOpenCourseService()->getTodayOpenLiveCourseNumber();
        $courseNumber = $liveCourseNumber + $openLiveCourseNumber;

        return $this->render(
            'my/teaching/lives-calendar.html.twig',
            array('courseNumber' => $courseNumber)
        );
    }

    public function livesAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        $courseSets = $this->getCourseSetService()->findLearnCourseSetsByUserId($currentUser['id']);
        $setIds = ArrayToolkit::column($courseSets, 'id');
        $courses = $this->getCourseService()->findCoursesByCourseSetIds($setIds);
        $courseIds = ArrayToolkit::column($courses, 'id');

        $conditions = array(
            'status' => 'published',
            'startTime_GE' => time(),
            'parentId' => 0,
            'courseIds' => $courseIds,
            'type' => 'live',
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getTaskService()->countTasks($conditions),
            10
        );

        $tasks = $this->getTaskService()->searchTasks(
            $conditions,
            array('startTime' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseSets = ArrayToolkit::index($courseSets, 'id');
        $courses = ArrayToolkit::index($courses, 'id');

        $newCourseSets = array();
        if (!empty($courseSets)) {
            foreach ($tasks as $key => &$task) {
                $course = $courses[$task['courseId']];
                $courseSetId = $course['courseSetId'];
                $newCourseSets[$courseSetId] = $courseSets[$courseSetId];
                $newCourseSets[$courseSetId]['task'] = $task;
            }
        }

        $default = $this->getSettingService()->get('default', array());

        return $this->render(
            'my/learning/course-set/live-list.html.twig',
            array(
                'courseSets' => $newCourseSets,
                'paginator' => $paginator,
                'default' => $default,
            )
        );
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }
}
