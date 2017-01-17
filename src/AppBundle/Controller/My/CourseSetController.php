<?php


namespace AppBundle\Controller\My;


use AppBundle\Controller\Course\CourseBaseController;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

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

        return $this->render('my/learning/course-set/favorite.html.twig', array(
            'courseFavorites' => $courseFavorites,
            'paginator'       => $paginator
        ));
    }

    public function teachingAction(Request $request, $filter = 'normal')
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $conditions = array(
            'type' => 'normal'
        );

        if ($filter == 'live') {
            $conditions['type'] = 'live';
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countUserTeachingCourseSets($user['id'], $conditions),
            20
        );

        $sets = $this->getCourseSetService()->searchUserTeachingCourseSets(
            $user['id'],
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $service = $this->getCourseService();
        $sets    = array_map(function ($set) use ($user, $service) {
            $set['canManage'] = $set['creator'] == $user['id'];
            $set['courses']   = $service->findUserTeachingCoursesByCourseSetId($set['id'], false);
            return $set;
        }, $sets);

        return $this->render('my/teaching/course-sets.html.twig', array(
            'courseSets' => $sets,
            'paginator'  => $paginator,
            'filter'     => $filter
        ));
    }

    public function livesAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        $courseSets = $this->getCourseSetService()->findLearnCourseSetsByUserId($currentUser['id']);
        $setIds     = ArrayToolkit::column($courseSets, 'id');
        $courses    = $this->getCourseService()->findCoursesByCourseSetIds($setIds);
        $courseIds  = ArrayToolkit::column($courses, 'id');

        $conditions = array(
            'status'       => 'published',
            'startTime_GE' => time(),
            'courseIds'    => $courseIds,
            'type'         => 'live'
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getTaskService()->count($conditions),
            10
        );

        $tasks = $this->getTaskService()->search(
            $conditions,
            array('startTime' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseSets = ArrayToolkit::index($courseSets, 'id');
        $courses    = ArrayToolkit::index($courses, 'id');

        $newCourseSets = array();
        if (!empty($courseSets)) {
            foreach ($tasks as $key => &$task) {
                $course                              = $courses[$task['courseId']];
                $courseSetId                         = $course['courseSetId'];
                $newCourseSets[$courseSetId]         = $courseSets[$courseSetId];
                $newCourseSets[$courseSetId]['task'] = $task;
            }
        }

        $default = $this->getSettingService()->get('default', array());
        return $this->render('my/learning/course-set/live-list.html.twig', array(
            'courseSets' => $newCourseSets,
            'paginator'  => $paginator,
            'default'    => $default
        ));
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
}