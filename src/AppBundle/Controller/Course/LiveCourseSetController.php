<?php


namespace AppBundle\Controller\Course;


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

        $futureLiveCourses = array();

        if (!empty($futureLiveCourseIds)) {
            $pageCourseIds     = array_slice($futureLiveCourseIds, $paginator->getOffsetCount(), $paginator->getPerPageCount());
            $futureLiveCourses = $this->getCourseService()->findCoursesByIds($pageCourseIds);

            $futureLiveCourses = ArrayToolkit::index($futureLiveCourses, 'id');
            $futureLiveCourses = $this->_liveCourseSort($futureLiveCourseIds, $futureLiveCourses, 'future');
        }

        $replayLiveCourses = array();

        if (count($futureLiveCourses) < $paginator->getPerPageCount()) {
            $conditions['courseIds'] = $futureLiveCourseIds;
            //$replayLiveCourses       = $this->_searchReplayLiveCourse($request, $conditions, $futureLiveCourseIds, $futureLiveCourses);
            $replayLiveCourses = array();
        }

        $liveCourses = array_merge($futureLiveCourses, $replayLiveCourses);

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

    private function _liveCourseSort($liveLessonCourseIds, $liveCourses, $type)
    {
        $courses = array();

        if (empty($liveCourses)) {
            return array();
        }

        foreach ($liveLessonCourseIds as $key => $courseId) {
            if (isset($liveCourses[$courseId])) {
                $courses[$courseId] = $liveCourses[$courseId];

                if ($type == 'future') {
                    $lessons = $this->getCourseService()->searchLessons(array('courseId' => $courseId, 'endTimeGreaterThan' => time()), array('startTime', 'ASC'), 0, 1);
                } else {
                    $lessons = $this->getCourseService()->searchLessons(array('courseId' => $courseId, 'endTimeLessThan' => time()), array('startTime', 'DESC'), 0, 1);
                }

                $courses[$courseId]['liveStartTime'] = $lessons[0]['startTime'];
                $courses[$courseId]['liveEndTime']   = $lessons[0]['endTime'];
                $courses[$courseId]['lessonId']      = $lessons[0]['id'];
            }
        }

        return $courses;
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}