<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\Paginator;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class LiveCourseSetController extends CourseBaseController
{
    public function courseSetsBlockAction($courseSets, $view = 'list', $mode = 'default')
    {
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(ArrayToolkit::column($courseSets, 'id'));

        return $this->forward('AppBundle:Course/LiveCourseSet:coursesBlock', array(
            'courses' => $courses,
            'view' => $view,
            'mode' => $mode,
        ));
    }

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
            'users' => $users,
            'mode' => $mode,
        ));
    }

    public function exploreAction(Request $request)
    {
        if (!$this->setting('course.live_course_enabled')) {
            return $this->createMessageResponse('info', $this->get('translator')->trans('直播频道已关闭'));
        }

        $recentTasksCondition = array(
            'status' => 'published',
            'endTime_GT' => time(),
            'type' => 'live',
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getTaskService()->countTasks($recentTasksCondition),
            30
        );

        $recentTasks = $this->getTaskService()->searchTasks(
            $recentTasksCondition,
            array('startTime' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($recentTasks, 'fromCourseSetId'));
        $courseSets = ArrayToolkit::index($courseSets, 'id');
        $recentCourseSets = array();

        foreach ($recentTasks as $task) {
            $courseSet = $courseSets[$task['fromCourseSetId']];

            if ($courseSet['status'] != 'published' || $courseSet['parentId'] != '0') {
                continue;
            }

            $courseSet['task'] = $task;
            $recentCourseSets[] = $courseSet;
        }

        $liveCourseSets = $this->getCourseSetService()->searchCourseSets(array(
            'status' => 'published',
            'type' => 'live',
            'parentId' => '0',
        ), 'lastest', 0, 10);

        $liveCourses = $this->getCourseService()->findCoursesByCourseSetIds(ArrayToolkit::column($liveCourseSets, 'id'));

        $userIds = array();
        foreach ($liveCourses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);
        $default = $this->getSettingService()->get('default', array());

        return $this->render('course-set/live/explore.html.twig', array(
            'recentCourseSets' => $recentCourseSets,
            'liveCourseSets' => $liveCourseSets,
            'users' => $users,
            'paginator' => $paginator,
            'default' => $default,
        ));
    }

    public function replayListAction()
    {
        $publishedCourseSetIds = $this->_findPublishedLiveCourseSetIds();

        $liveReplayList = $this->getTaskService()->searchTasks(array(
            'endTime_LT' => time(),
            'type' => 'live',
            'copyId' => 0,
            'status' => 'published',
            'fromCourseSetIds' => $publishedCourseSetIds,
        ), array('startTime' => 'DESC'), 0, 10);

        return $this->render('course-set/live/replay-list.html.twig', array(
            'liveReplayList' => $liveReplayList,
        ));
    }

    public function liveTabAction()
    {
        $taskDates = $this->getTaskService()->findFutureLiveDates();
        $currentLiveTasks = $this->getTaskService()->findCurrentLiveTasks();
        $futureLiveLessons = $this->getTaskService()->findFutureLiveTasks();

        $liveTabs['today']['current'] = $currentLiveTasks;
        $liveTabs['today']['future'] = $futureLiveLessons;

        $dateTabs = array('today');
        $today = date('Y-m-d');

        foreach ($taskDates as $key => &$value) {
            if ($today == $value['date']) {
                continue;
            } else {
                $dayTasks = $futureLiveLessons = $this->getTaskService()->searchTasks(array(
                    'startTime_GE' => strtotime($value['date']),
                    'endTime_LT' => strtotime($value['date'].' 23:59:59'),
                    'type' => 'live',
                    'status' => 'published',
                ), array('startTime' => 'ASC'), 0, PHP_INT_MAX);

                $date = date('m-d', strtotime($value['date']));
                $liveTabs[$date]['future'] = $dayTasks;
                $dateTabs[] = $date;
            }
        }

        return $this->render('course-set/live/tab.html.twig', array(
            'liveTabs' => $liveTabs,
            'dateTabs' => $dateTabs,
        ));
    }

    public function liveCourseSetsAction(Request $request)
    {
        $categoryId = $request->query->get('categoryId', '');
        $vipCategoryId = $request->query->get('vipCategoryId', '');
        $currentPage = $request->query->get('page', 1);



        $vipCourseSetIds = $this->_findVipCourseSetIds($vipCategoryId);
        $futureLiveCourseSets = $this->_findFutureLiveCourseSets($vipCourseSetIds);

        $paginator = new Paginator(
            $request,
            $this->getCourseSetService()->countCourseSets(array('ids' => $vipCourseSetIds)),
            10
            );
        $replayLiveCourseSets = array();
        if (count($futureLiveCourseSets) < $paginator->getPerPageCount()) {
            $futureLiveCourseSetIds = ArrayToolkit::column($futureLiveCourseSets, 'id');
            $replayLiveCourseSetIds = array_diff($vipCourseSetIds, $futureLiveCourseSetIds);
            $replayLiveCourseSets = $this->_findReplayLiveCourseSets($currentPage, $replayLiveCourseSetIds);
        }

        $liveCourseSets = array_merge($futureLiveCourseSets, $replayLiveCourseSets);
        $liveCourseSets = ArrayToolkit::index($liveCourseSets, 'id');
        $liveCourseSetIds = array_keys($liveCourseSets);
        $liveCourseSets = $this->_fillLiveCourseSetAttribute($liveCourseSetIds, $liveCourseSets, 'future');

        $levels = array();
        if ($this->isPluginInstalled('Vip')) {
            $levels = ArrayToolkit::index($this->getLevelService()->searchLevels(array('enabled' => 1), array(),0, 100), 'id');
        }

        return $this->render('course-set/live/all-list.html.twig', array(
            'liveCourseSets' => $liveCourseSets,
            'paginator' => $paginator,
            'request' => $request,
            'levels' => $levels,
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
            $ret[$courseSetId] = $liveCourseSets[$courseSetId];
            $tasks = $this->getTaskService()->searchTasks(array('fromCourseSetId' => $courseSetId), array('startTime' => 'ASC'), 0, 1);
            $ret[$courseSetId]['course'] = $courses[$courseSetId];
            $ret[$courseSetId]['liveStartTime'] = $tasks[0]['startTime'];
            $ret[$courseSetId]['liveEndTime'] = $tasks[0]['endTime'];
            $ret[$courseSetId]['taskId'] = $tasks[0]['id'];
        }
        return $ret;
    }

    private function _findVipCourseSetIds($vipLevelId)
    {
        $preLevelIds = ArrayToolkit::column($this->getLevelService()->findPrevEnabledLevels($vipLevelId), 'id');

        if(!empty($vipLevelId)) {
        $preLevelIds = array_merge($preLevelIds, array($vipLevelId));
        }
        $vipCourseConditions = array(
            'status' => 'published',
            'parentId' => 0,
            'vipLevelIds' => $preLevelIds
        );

        $vipCourses = $this->getCourseService()->searchCourses(
            $vipCourseConditions,
            'latest',
            0,
            20
        );
        $vipCourseSetIds = ArrayToolkit::column($vipCourses, 'courseSetId');

        return $vipCourseSetIds;
    }

    private function _findFutureLiveCourseSets($vipCourseSetIds)
    {
        $futureLiveTasks = $this->getTaskService()->findFutureLiveTasks();
        $futureCourseSetIds = ArrayToolkit::column($futureLiveTasks, 'fromCourseSetId');
        $futureLiveCourseSetIds = array_intersect($futureCourseSetIds, $vipCourseSetIds);
        if (empty($futureLiveCourseSetIds)) {
            $futureLiveCourseSetIds = array(-1);
        }
        $condition = array(
            'status' => 'published',
            'type' => 'live',
            'ids' => $futureLiveCourseSetIds
        );
        $futureLiveCourseSets = $this->getCourseSetService()->searchCourseSets(
            $condition,
            'latest',
            0,
            100
        );

        return $futureLiveCourseSets;
    }

    private function _findReplayLiveCourseSets($currentPage,$replayLiveCourseSetIds)
    {
        $pageSize = 10;

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

        $conditions = array(
            'ids' => $replayLiveCourseSetIds,
            'type' => 'live',
            'status' => 'published'
        );
        $replayLiveCourseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('createdTime' => 'DESC'),
            $start,
            $limit
        );

        return $replayLiveCourseSets;
    }


    private function _findPublishedLiveCourseSetIds()
    {
        $conditions = array(
            'status' => 'published',
            'type' => 'live',
            'parentId' => 0,
        );
        $publishedCourseSets = $this->getCourseSetService()->searchCourseSets($conditions, array('createdTime' => 'DESC'), 0, PHP_INT_MAX);

        return ArrayToolkit::column($publishedCourseSets, 'id');
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
     * @return LevelService
     */
    protected function getLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }
}
