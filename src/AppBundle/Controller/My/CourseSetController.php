<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\Course\CourseBaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Favorite\Service\FavoriteService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CourseSetController extends CourseBaseController
{
    public function favoriteAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $paginator = new Paginator(
            $request,
            $this->getFavoriteService()->countFavorites(['userId' => $user['id'], 'targetTypes' => ['course', 'openCourse']]),
            12
        );

        $courseFavorites = $this->getFavoriteService()->searchFavorites(
            ['userId' => $user['id'], 'targetTypes' => ['course', 'openCourse']],
            ['id' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render(
            'my/learning/course-set/favorite.html.twig',
            [
                'courseFavorites' => $courseFavorites,
                'paginator' => $paginator,
            ]
        );
    }

    public function teachingAction(Request $request, $filter = 'normal')
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $conditions = [
            'type' => $filter,
            'parentId' => 0,
        ];

        if ('classroom' == $filter) {
            $conditions['parentId_GT'] = 0;
            unset($conditions['type']);
            unset($conditions['parentId']);
        }

        $paginator = new Paginator(
            $request,
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
        $that = $this;
        $courseSets = array_map(
            function ($set) use ($user, $service, $that) {
                $courseNum = $service->countCourses([
                    'courseSetId' => $set['id'],
                ]);

                if ($courseNum > 1) {
                    $set['redirect_path'] = $that->generateUrl('course_set_manage_courses', ['courseSetId' => $set['id']]);
                } else {
                    $courses = $service->findCoursesByCourseSetId($set['id']);
                    $set['redirect_path'] = $that->generateUrl('course_set_manage_course_info', ['courseSetId' => $set['id'], 'courseId' => $courses['0']['id']]);
                }

                $set['courseNum'] = $courseNum;

                return $set;
            },
            $courseSets
        );

        $classrooms = [];

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
            [
                'courseSets' => $courseSets,
                'classrooms' => $classrooms,
                'paginator' => $paginator,
                'filter' => $filter,
            ]
        );
    }

    public function generateUrl($route, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
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
            ['courseNumber' => $courseNumber]
        );
    }

    public function livesAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        $courseSets = $this->getCourseSetService()->findLearnCourseSetsByUserId($currentUser['id']);
        $setIds = ArrayToolkit::column($courseSets, 'id');
        $courses = $this->getCourseService()->findCoursesByCourseSetIds($setIds);
        $courseIds = ArrayToolkit::column($courses, 'id');

        $conditions = [
            'status' => 'published',
            'startTime_GE' => time(),
            'parentId' => 0,
            'courseIds' => $courseIds,
            'type' => 'live',
        ];

        $paginator = new Paginator(
            $request,
            $this->getTaskService()->countTasks($conditions),
            10
        );

        $tasks = $this->getTaskService()->searchTasks(
            $conditions,
            ['startTime' => 'ASC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseSets = ArrayToolkit::index($courseSets, 'id');
        $courses = ArrayToolkit::index($courses, 'id');
        $tasks = ArrayToolkit::group($tasks, 'courseId');
        $newCourseSets = [];
        if (!empty($courseSets)) {
            foreach ($tasks as $key => &$task) {
                $course = $courses[$key];
                $courseSetId = $course['courseSetId'];
                $newCourseSets[$courseSetId] = $courseSets[$courseSetId];
                $newCourseSets[$courseSetId]['task'] = $task[0];
            }
        }

        $default = $this->getSettingService()->get('default', []);

        return $this->render(
            'my/learning/course-set/live-list.html.twig',
            [
                'courseSets' => $newCourseSets,
                'paginator' => $paginator,
                'default' => $default,
            ]
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

    /**
     * @return FavoriteService
     */
    protected function getFavoriteService()
    {
        return $this->createService('Favorite:FavoriteService');
    }
}
