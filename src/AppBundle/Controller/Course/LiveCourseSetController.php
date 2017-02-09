<?php


namespace AppBundle\Controller\Course;


use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class LiveCourseSetController extends CourseBaseController
{
    public function coursesBlockAction($courses, $view = 'list', $mode = 'default')
    {
        $userIds = array();

        foreach ($courses as $course) {
            $userIds = array_merge($userIds, empty($course['teacherIds']) ? array() : $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        foreach ($courses as &$course) {
            if (empty($course['id'])) {
                $course = array();
            }
        }

        $courses = array_filter($courses);

        return $this->render("course/block/courses-block-{$view}.html.twig", array(
            'courses' => $courses,
            'users'   => $users,
            'mode'    => $mode
        ));
    }

    public function exploreAction(Request $request)
    {
        if (!$this->setting('course.live_course_enabled')) {
            return $this->createMessageResponse('info', $this->get('translator')->trans('直播频道已关闭'));
        }

        $recentTasksCondition = array(
            'status'     => 'published',
            'endTime_GT' => time(),
            'type'       => 'live'
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getTaskService()->countTasks($recentTasksCondition)
            , 30
        );

        $recentTasks = $this->getTaskService()->searchTasks(
            $recentTasksCondition,
            array('startTime' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseSets       = $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($recentTasks, 'fromCourseSetId'));
        $courseSets       = ArrayToolkit::index($courseSets, 'id');
        $recentCourseSets = array();

        foreach ($recentTasks as $task) {
            $courseSet = $courseSets[$task['fromCourseSetId']];

            if ($courseSet['status'] != 'published' || $courseSet['parentId'] != '0') {
                continue;
            }

            $courseSet['task']  = $task;
            $recentCourseSets[] = $courseSet;
        }

        $liveCourseSets = $this->getCourseSetService()->searchCourseSets(array(
            'status'   => 'published',
            'type'     => 'live',
            'parentId' => '0'
        ), 'lastest', 0, 10);

        $liveCourses = $this->getCourseService()->findCoursesByCourseSetIds(ArrayToolkit::column($liveCourseSets, 'id'));

        $userIds = array();
        foreach ($liveCourses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }

        $users   = $this->getUserService()->findUsersByIds($userIds);
        $default = $this->getSettingService()->get('default', array());
        return $this->render('course-set/live/explore.html.twig', array(
            'recentCourseSets' => $recentCourseSets,
            'liveCourseSets'   => $liveCourseSets,
            'users'            => $users,
            'paginator'        => $paginator,
            'default'          => $default
        ));
    }

    public function replayListAction()
    {
        $publishedCourseSetIds = $this->_findPublishedLiveCourseSetIds();

        $liveReplayList = $this->getTaskService()->searchTasks(array(
            'endTime_LT'       => time(),
            'type'             => 'live',
            'copyId'           => 0,
            'status'           => 'published',
            'fromCourseSetIds' => $publishedCourseSetIds
        ), array('startTime' => 'DESC'), 0, 10);

        return $this->render('course-set/live/replay-list.html.twig', array(
            'liveReplayList' => $liveReplayList

        ));
    }

    public function liveTabAction()
    {
        $courseSets = $this->getCourseSetService()->searchCourseSets(array(
            'type'     => 'live',
            'status'   => 'published',
            'parentId' => 0,
            'locked'   => 0
        ), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);

        $courseSetIds = ArrayToolkit::column($courseSets, 'id');

        $taskDates         = $this->getTaskService()->findFutureLiveDatesByCourseSetIdsGroupByDate($courseSetIds, 4);
        $currentLiveTasks  = $this->getTaskService()->findCurrentLiveTasks();
        $futureLiveLessons = $this->getTaskService()->findFutureLiveTasks();

        $liveTabs['today']['current'] = $currentLiveTasks;
        $liveTabs['today']['future']  = $futureLiveLessons;

        $dateTabs = array('today');
        $today    = date("Y-m-d");

        foreach ($taskDates as $key => &$value) {
            if ($today == $value['date'] || count($liveTabs) >= 4) {
                continue;
            } else {
                $dayTasks = $futureLiveLessons = $this->getTaskService()->searchTasks(array(
                    'startTimeGreaterThan' => strtotime($value['date']),
                    'endTimeLessThan'      => strtotime($value['date'] . ' 23:59:59'),
                    'type'                 => 'live',
                    'fromCourseSetIds'     => $courseSetIds,
                    'status'               => 'published'
                ), array('startTime' => 'ASC'), 0, PHP_INT_MAX);

                $date                      = date('m-d', strtotime($value['date']));
                $liveTabs[$date]['future'] = $dayTasks;
                $dateTabs[]                = $date;
            }
        }

        return $this->render('course-set/live/tab.html.twig', array(
            'liveTabs' => $liveTabs,
            'dateTabs' => $dateTabs
        ));
    }

    public function liveCourseSetsAction(Request $request)
    {
        $conditions = array(
            'status'   => 'published',
            'type'     => 'live',
            'parentId' => 0
        );

        $categoryId = $request->query->get('categoryId', '');

        if (!empty($categoryId)) {
            $conditions['categoryId'] = $categoryId;
        }

        $vipCategoryId = $request->query->get('vipCategoryId', '');

        if (!empty($vipCategoryId)) {
            $conditions['vipLevelId'] = $vipCategoryId;
        }

        $paginator = new Paginator(
            $request,
            $this->getCourseSetService()->countCourseSets($conditions),
            10
        );

        $futureLiveTasks        = $this->getTaskService()->findFutureLiveTasks();
        $futureLiveCourseSetIds = ArrayToolkit::column($futureLiveTasks, 'fromCourseSetId');

        $pageFutureLiveCourseSets = array();

        if (!empty($futureLiveCourseSetIds)) {
            $pageCourseSetIds         = array_slice($futureLiveCourseSetIds, $paginator->getOffsetCount(), $paginator->getPerPageCount());
            $pageFutureLiveCourseSets = $this->getCourseSetService()->findCourseSetsByCourseIds($pageCourseSetIds);

            $pageFutureLiveCourseSets = ArrayToolkit::index($pageFutureLiveCourseSets, 'id');
            $pageFutureLiveCourseSets = $this->_fillLiveCourseSetAttribute($futureLiveCourseSetIds, $pageFutureLiveCourseSets, 'future');
        }

        $replayLiveCourseSets = array();

        if (count($pageFutureLiveCourseSets) < $paginator->getPerPageCount()) {
            $conditions['ids']    = $futureLiveCourseSetIds;
            $replayLiveCourseSets = $this->_searchReplayLiveCourseSets($request, $conditions, $futureLiveCourseSetIds);
        }

        $liveCourseSets = array_merge($pageFutureLiveCourseSets, $replayLiveCourseSets);

        $levels = array();

        if ($this->isPluginInstalled('Vip')) {
            $levels = ArrayToolkit::index($this->getLevelService()->searchLevels(array('enabled' => 1), 0, 100), 'id');
        }

        return $this->render('course-set/live/all-list.html.twig', array(
            'liveCourseSets' => $liveCourseSets,
            'paginator'      => $paginator,
            'request'        => $request,
            'levels'         => $levels
        ));
    }

    private function _fillLiveCourseSetAttribute($allLiveLessonCourseIds, $liveCourseSets, $type)
    {
        if (empty($liveCourseSets)) {
            return array();
        }
        $courses = $this->getCourseService()->findCoursesByCourseSetIds($allLiveLessonCourseIds);
        $courses = ArrayToolkit::index($courses, 'courseSetId');
        $ret = array();
        foreach ($allLiveLessonCourseIds as $key => $courseSetId) {
            if (isset($liveCourseSets[$courseSetId])) {
                $ret[$courseSetId] = $liveCourseSets[$courseSetId];

                if ($type == 'future') {
                    $tasks = $this->getTaskService()->searchTasks(array('fromCourseSetId' => $courseSetId, 'endTime_GT' => time()), array('startTime' => 'ASC'), 0, 1);
                } else {
                    $tasks = $this->getTaskService()->searchTasks(array('fromCourseSetId' => $courseSetId, 'endTime_LT' => time()), array('startTime' => 'DESC'), 0, 1);
                }

                $ret[$courseSetId]['course'] = $courses[$courseSetId];
                $ret[$courseSetId]['liveStartTime'] = $tasks[0]['startTime'];
                $ret[$courseSetId]['liveEndTime']   = $tasks[0]['endTime'];
                $ret[$courseSetId]['taskId']      = $tasks[0]['id'];
            }
        }

        return $ret;
    }

    private function _findPublishedLiveCourseSetIds()
    {
        $conditions          = array(
            'status'   => 'published',
            'type'     => 'live',
            'parentId' => 0
        );
        $publishedCourseSets = $this->getCourseSetService()->searchCourseSets($conditions, array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
        return ArrayToolkit::column($publishedCourseSets, 'id');
    }

    private function _searchReplayLiveCourseSets(Request $request, $conditions, $allFutureLiveCourseSetIds)
    {
        $pageSize    = 10;
        $currentPage = $request->query->get('page', 1);

        if (isset($conditions['ids'])) {
            $futureLiveCourseSetsCount = $this->getCourseSetService()->countCourseSets($conditions);
        } else {
            $futureLiveCourseSetsCount = 0;
        }

        $pages = $futureLiveCourseSetsCount <= $pageSize ? 1 : floor($futureLiveCourseSetsCount / $pageSize);

        if ($pages == $currentPage) {
            $start = 0;
            $limit = $pageSize - ($futureLiveCourseSetsCount % $pageSize);
        } else {
            $start = ($currentPage - 1) * $pageSize;
            $limit = $pageSize;
        }

        $replayLiveCourseSetIds = $this->getTaskService()->findPastLivedCourseSetIds();

        unset($conditions['ids']);
        $conditions['excludeIds'] = $allFutureLiveCourseSetIds;

        $replayLiveCourses = $this->getCourseSetService()->searchCourseSets($conditions, array('createdTime' => 'DESC'), $start, $limit);

        $replayLiveCourses = ArrayToolkit::index($replayLiveCourses, 'id');
        $replayLiveCourses = $this->_fillLiveCourseSetAttribute($replayLiveCourseSetIds, $replayLiveCourses, 'replay');

        return $replayLiveCourses;
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