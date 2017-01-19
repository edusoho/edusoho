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
            $this->getTaskService()->count($recentTasksCondition)
            , 30
        );

        $recentTasks = $this->getTaskService()->search(
            $recentTasksCondition,
            array('startTime' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courses          = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($recentTasks, 'courseId'));
        $courses          = ArrayToolkit::index($courses, 'id');
        $courseSets       = $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($courses, 'courseSetId'));
        $courseSets       = ArrayToolkit::index($courseSets, 'id');
        $recentCourseSets = array();

        foreach ($recentTasks as $task) {
            $course    = $courses[$task['courseId']];
            $courseSet = $courseSets[$course['courseSetId']];

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
        $publishedCourseSetIds = $this->_findPublishedLiveCourseIds();
        $courses               = $this->getCourseService()->findCoursesByCourseSetIds($publishedCourseSetIds);
        $publishedCourseIds    = ArrayToolkit::column($courses, 'id');

        $liveReplayList = $this->getTaskService()->search(array(
            'endTime_LT' => time(),
            'type'       => 'live',
            'copyId'     => 0,
            'status'     => 'published',
            'courseIds'  => $publishedCourseIds
        ), array('startTime' => 'DESC'), 0, 10);

        return $this->render('course-set/live/replay-list.html.twig', array(
            'liveReplayList' => $liveReplayList

        ));
    }

    public function liveTabAction()
    {
        $courseSets   = $this->getCourseSetService()->searchCourseSets(array(
            'type'     => 'live',
            'status'   => 'published',
            'parentId' => 0,
            'locked'   => 0
        ), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
        $courseSetIds = ArrayToolkit::column($courseSets, 'id');
        $courseIds    = ArrayToolkit::column($this->getCourseService()->findCoursesByCourseSetIds($courseSetIds), 'id');

        $taskDates         = $this->getTaskService()->findFutureLiveDatesByCourseIdsGroupByDate($courseIds, 4);
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
                $dayLessons = $futureLiveLessons = $this->getTaskService()->search(array(
                    'startTimeGreaterThan' => strtotime($value['date']),
                    'endTimeLessThan'      => strtotime($value['date'] . ' 23:59:59'),
                    'type'                 => 'live',
                    'courseIds'            => $courseIds,
                    'status'               => 'published'
                ), array('startTime' => 'ASC'), 0, PHP_INT_MAX);

                $date                      = date('m-d', strtotime($value['date']));
                $liveTabs[$date]['future'] = $dayLessons;
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

        $futureLiveTasks     = $this->getTaskService()->findFutureLiveTasks();
        $futureLiveCourseIds = ArrayToolkit::column($futureLiveTasks, 'courseId');

        $futureLiveCourseSets = array();

        if (!empty($futureLiveCourseIds)) {
            $pageCourseIds        = array_slice($futureLiveCourseIds, $paginator->getOffsetCount(), $paginator->getPerPageCount());
            $futureLiveCourseSets = $this->getCourseSetService()->findCourseSetsByCourseIds($pageCourseIds);

            $futureLiveCourseSets = ArrayToolkit::index($futureLiveCourseSets, 'id');
            $futureLiveCourseSets = $this->_fillLiveCourseSetAttribute($futureLiveCourseIds, $futureLiveCourseSets, 'future');
        }

        $replayLiveCourses = array();

        if (count($futureLiveCourseSets) < $paginator->getPerPageCount()) {
            $conditions['courseIds'] = $futureLiveCourseIds;
            $replayLiveCourses       = $this->_searchReplayLiveCourse($request, $conditions, $futureLiveCourseIds);
        }

        $liveCourses = array_merge($futureLiveCourseSets, $replayLiveCourses);

        $levels = array();

        if ($this->isPluginInstalled('Vip')) {
            $levels = ArrayToolkit::index($this->getLevelService()->searchLevels(array('enabled' => 1), 0, 100), 'id');
        }

        return $this->render('course-set/live/all-list.html.twig', array(
            'liveCourses' => $liveCourses,
            'paginator'   => $paginator,
            'request'     => $request,
            'levels'      => $levels
        ));
    }

    private function _fillLiveCourseSetAttribute($liveLessonCourseIds, $liveCourses, $type)
    {
        foreach ($liveCourseSets as $id => &$courseSet) {
            if (isset($liveCourseSets[$courseSetId])) {
                $ret[$courseSetId] = $liveCourseSets[$courseSetId];

                if ($type == 'future') {
                    $tasks = $this->getTaskService()->search(array('courseId' => $courseSetId, 'endTime_GT' => time()), array('startTime' => 'ASC'), 0, 1);
                } else {
                    $tasks = $this->getTaskService()->search(array('courseId' => $courseSetId, 'endTime_LT' => time()), array('startTime' => 'DESC'), 0, 1);
                }

                $ret[$courseSetId]['liveStartTime'] = $tasks[0]['startTime'];
                $ret[$courseSetId]['liveEndTime']   = $tasks[0]['endTime'];
                $ret[$courseSetId]['lessonId']      = $tasks[0]['id'];
            }
        }
    }

    private function _findPublishedLiveCourseIds()
    {
        $conditions          = array(
            'status'   => 'published',
            'type'     => 'live',
            'parentId' => 0
        );
        $publishedCourseSets = $this->getCourseSetService()->searchCourseSets($conditions, array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
        return ArrayToolkit::column($publishedCourseSets, 'id');
    }

    private function _searchReplayLiveCourse(Request $request, $conditions, $allFutureLiveCourseIds)
    {
        $pageSize    = 10;
        $currentPage = $request->query->get('page', 1);

        $futureLiveCoursesCount = 0;

        if (isset($conditions['courseIds'])) {
            $futureLiveCoursesCount = $this->getCourseService()->searchCourseCount($conditions);
        }

        $pages = $futureLiveCoursesCount <= $pageSize ? 1 : floor($futureLiveCoursesCount / $pageSize);

        if ($pages == $currentPage) {
            $start = 0;
            $limit = $pageSize - ($futureLiveCoursesCount % $pageSize);
        } else {
            $start = ($currentPage - 1) * $pageSize;
            $limit = $pageSize;
        }

        $replayLiveCourseIds = $this->getCourseService()->findPastLivedCourseIds();

        unset($conditions['courseIds']);
        $conditions['excludeIds'] = $allFutureLiveCourseIds;

        $replayLiveCourses = $this->getCourseService()->searchCourses($conditions, array('createdTime' => 'DESC'), $start, $limit);

        $replayLiveCourses = ArrayToolkit::index($replayLiveCourses, 'id');
        $replayLiveCourses = $this->_fillLiveCourseSetAttribute($replayLiveCourseIds, $replayLiveCourses, 'replay');

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