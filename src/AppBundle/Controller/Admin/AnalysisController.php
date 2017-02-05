<?php
namespace AppBundle\Controller\Admin;

use Topxia\Common\Paginator;
use Topxia\Common\DateToolkit;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class AnalysisController extends BaseController
{
    public function routeAnalysisDataTypeAction(Request $request, $tab)
    {
        $analysisDateType = $request->query->get("analysisDateType");
        return $this->forward("AppBundle:Admin/Analysis:{$analysisDateType}", array(
            'request' => $request,
            'tab'     => $tab
        ));
    }

    public function registerAction(Request $request, $tab)
    {
        $data              = array();
        $count             = 0;
        $registerStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $paginator = new Paginator(
            $request,
            $this->getUserService()->searchUserCount($timeRange),
            20
        );

        $registerDetail = $this->getUserService()->searchUsers(
            $timeRange,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $registerData = "";

        if ($tab == "trend") {
            $registerData = $this->getUserService()->analysisRegisterDataByTime($timeRange['startTime'], $timeRange['endTime']);
            $data         = $this->fillAnalysisData($condition, $registerData);
            foreach ($registerData as $key => $value) {
                $count += $value['count'];
            }
        }

        $registerStartData = $this->getUserService()->searchUsers(array(), array('createdTime' => 'ASC'), 0, 1);

        if ($registerStartData) {
            $registerStartDate = date("Y-m-d", $registerStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        $registerIds      = ArrayToolkit::column($registerDetail, 'id');
        $registerProfiles = $this->getUserService()->findUserProfilesByIds($registerIds);

        return $this->render("admin/operation-analysis/register.html.twig", array(
            'registerDetail'    => $registerDetail,
            'paginator'         => $paginator,
            'tab'               => $tab,
            'registerProfiles'  => $registerProfiles,
            'data'              => $data,
            "registerStartDate" => $registerStartDate,
            "dataInfo"          => $dataInfo,
            'count'             => $count
        ));
    }

    public function userSumAction(Request $request, $tab)
    {
        $data             = array();
        $userSumStartDate = "";
        $userSumDetail    = array();

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $result = array(
            'tab' => $tab
        );

        if ($tab == "trend") {
            $registerData  = $this->getUserService()->analysisRegisterDataByTime($timeRange['startTime'], $timeRange['endTime']);
            $userInitCount = $this->getUserService()->countUsers(
                array('endTime' => $timeRange['startTime'])
            );
            $data           = $this->fillAnalysisSum($condition, $registerData, $userInitCount);
            $result["data"] = $data;
        } else {
            $paginator = new Paginator(
                $request,
                $this->getUserService()->searchUserCount($timeRange),
                20
            );

            $userSumDetail = $this->getUserService()->searchUsers(
                $timeRange,
                array('createdTime' => 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
            $result['userSumDetail'] = $userSumDetail;
            $result['paginator']     = $paginator;
        }

        $userSumStartData = $this->getUserService()->searchUsers(array(), array('createdTime' => 'ASC'), 0, 1);

        if ($userSumStartData) {
            $userSumStartDate = date("Y-m-d", $userSumStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        $result['userSumStartDate'] = $userSumStartDate;
        $result['dataInfo']         = $dataInfo;

        $userSumIds                = ArrayToolkit::column($userSumDetail, 'id');
        $userSumProfiles           = $this->getUserService()->findUserProfilesByIds($userSumIds);
        $result['userSumProfiles'] = $userSumProfiles;
        return $this->render("admin/operation-analysis/user-sum.html.twig", $result);
    }

    public function courseSetSumAction(Request $request, $tab)
    {
        $data                  = array();
        $courseSetSumStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $count = $this->getCourseSetService()->countCourseSets($timeRange);

        $timeRange['parentId'] = 0;
        $paginator             = new Paginator(
            $request,
            $count,
            20
        );

        $courseSetSumDetail = $this->getCourseSetService()->searchCourseSets(
            $timeRange,
            '',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseSetInitSum = "";

        if ($tab == "trend") {
            $courseSetData    = $this->getCourseSetService()->analysisCourseSetDataByTime($timeRange['startTime'], $timeRange['endTime']);
            $courseSetInitSum = $this->getCourseSetService()->countCourseSets(array('endTime' => $timeRange['startTime']));
            $data             = $this->fillAnalysisSum($condition, $courseSetData, $courseSetInitSum);
        }

        $userIds = ArrayToolkit::column($courseSetSumDetail, 'creator');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSetSumDetail, 'categoryId'));

        $courseSetSumStartData = $this->getCourseSetService()->searchCourseSets(array(), array('createdTime' => 'ASC'), 0, 1);

        if ($courseSetSumStartData) {
            $courseSetSumStartDate = date("Y-m-d", $courseSetSumStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/course-set-sum.html.twig", array(
            'courseSetSumDetail'    => $courseSetSumDetail,
            'paginator'             => $paginator,
            'tab'                   => $tab,
            'categories'            => $categories,
            'data'                  => $data,
            'users'                 => $users,
            'courseSetSumStartDate' => $courseSetSumStartDate,
            'dataInfo'              => $dataInfo
        ));
    }

    public function courseSumAction(Request $request, $tab)
    {
        $data               = array();
        $courseSumStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $count = $this->getCourseService()->countCourses($timeRange);

        $timeRange['parentId'] = 0;
        $paginator             = new Paginator(
            $request,
            $count,
            20
        );

        $courseSumDetail = $this->getCourseService()->searchCourses(
            $timeRange,
            '',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseSetSumData = "";

        if ($tab == "trend") {
            $courseData    = $this->getCourseService()->analysisCourseDataByTime($timeRange['startTime'], $timeRange['endTime']);
            $courseInitSum = $this->getCourseService()->countCourses(array('endTime' => $timeRange['startTime']));
            $data          = $this->fillAnalysisSum($condition, $courseData, $courseInitSum);
        }

        $userIds = ArrayToolkit::column($courseSumDetail, 'creator');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSumDetail, 'categoryId'));

        $courseStartData = $this->getCourseService()->searchCourses(array(), array('createdTime' => 'ASC'), 0, 1);

        if ($courseStartData) {
            $courseStartDate = date("Y-m-d", $courseStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/course-sum.html.twig", array(
            'courseSumDetail'    => $courseSumDetail,
            'paginator'          => $paginator,
            'tab'                => $tab,
            'categories'         => $categories,
            'data'               => $data,
            'users'              => $users,
            'courseSumStartDate' => $courseSumStartDate,
            'dataInfo'           => $dataInfo
        ));
    }

    public function loginAction(Request $request, $tab)
    {
        $data           = array();
        $loginStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $count = 0;

        $paginator = new Paginator(
            $request,
            $this->getLogService()->searchLogCount(array('action' => "login_success", 'startDateTime' => date("Y-m-d H:i:s", $timeRange['startTime']), 'endDateTime' => date("Y-m-d H:i:s", $timeRange['endTime']))),
            20
        );

        $loginDetail = $this->getLogService()->searchLogs(
            array('action' => "login_success", 'startDateTime' => date("Y-m-d H:i:s", $timeRange['startTime']), 'endDateTime' => date("Y-m-d H:i:s", $timeRange['endTime'])),
            'created',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $loginData = "";

        if ($tab == "trend") {
            $loginData = $this->getLogService()->analysisLoginDataByTime($timeRange['startTime'], $timeRange['endTime']);
            $data      = $this->fillAnalysisData($condition, $loginData);
            $count     = $this->getLogService()->analysisLoginNumByTime($timeRange['startTime'], $timeRange['endTime']);
        }

        $userIds = ArrayToolkit::column($loginDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $loginStartData = $this->getLogService()->searchLogs(array('action' => "login_success"), 'createdByAsc', 0, 1);

        if ($loginStartData) {
            $loginStartDate = date("Y-m-d", $loginStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/login.html.twig", array(
            'loginDetail'    => $loginDetail,
            'paginator'      => $paginator,
            'tab'            => $tab,
            'data'           => $data,
            'users'          => $users,
            'loginStartDate' => $loginStartDate,
            'dataInfo'       => $dataInfo,
            'count'          => $count
        ));
    }

    public function courseSetAction(Request $request, $tab)
    {
        $data               = array();
        $courseSetStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $count = $this->getCourseSetService()->countCourseSets($timeRange);

        $paginator = new Paginator(
            $request,
            $count,
            20
        );

        $courseSetDetail = $this->getCourseSetService()->searchCourseSets(
            $timeRange,
            '',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseSetData = "";

        if ($tab == "trend") {
            $courseSetData = $this->getCourseSetService()->analysisCourseSetDataByTime($timeRange['startTime'], $timeRange['endTime']);

            $data = $this->fillAnalysisData($condition, $courseSetData);
        }

        $userIds = ArrayToolkit::column($courseSetDetail, 'creator');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSetDetail, 'categoryId'));

        $courseSetStartData = $this->getCourseSetService()->searchCourseSets(array(), 'createdTimeByAsc', 0, 1);

        if ($courseSetStartData) {
            $courseSetStartDate = date("Y-m-d", $courseSetStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/course-set.html.twig", array(
            'courseSetDetail'    => $courseSetDetail,
            'paginator'          => $paginator,
            'tab'                => $tab,
            'categories'         => $categories,
            'data'               => $data,
            'users'              => $users,
            'courseSetStartDate' => $courseSetStartDate,
            'dataInfo'           => $dataInfo,
            'count'              => $count
        ));
    }

    public function lessonAction(Request $request, $tab)
    {
        $data            = array();
        $lessonStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->searchLessonCount($timeRange),
            20
        );

        $lessonDetail = $this->getCourseService()->searchLessons(
            $timeRange,
            array('createdTime', "desc"),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $lessonData = "";

        if ($tab == "trend") {
            $lessonData = $this->getCourseService()->analysisLessonDataByTime($timeRange['startTime'], $timeRange['endTime']);

            $data = $this->fillAnalysisData($condition, $lessonData);
        }

        $courseIds = ArrayToolkit::column($lessonDetail, 'courseId');

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $userIds = ArrayToolkit::column($courses, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $lessonStartData = $this->getCourseService()->searchLessons(array(), array('createdTime', "asc"), 0, 1);

        if ($lessonStartData) {
            $lessonStartDate = date("Y-m-d", $lessonStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/lesson.html.twig", array(
            'lessonDetail'    => $lessonDetail,
            'paginator'       => $paginator,
            'tab'             => $tab,
            'data'            => $data,
            'courses'         => $courses,
            'users'           => $users,
            'lessonStartDate' => $lessonStartDate,
            'dataInfo'        => $dataInfo
        ));
    }

    public function joinLessonAction(Request $request, $tab)
    {
        $data                = array();
        $joinLessonStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders(array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "status" => "paid")),
            20
        );

        $joinLessonDetail = $this->getOrderService()->searchOrders(
            array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "status" => "paid"),
            "latest",
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $joinLessonData = "";

        if ($tab == "trend") {
            $joinLessonData = $this->getOrderService()->analysisCourseOrderDataByTimeAndStatus($timeRange['startTime'], $timeRange['endTime'], "paid");

            $data = $this->fillAnalysisData($condition, $joinLessonData);
        }

        $courseIds = ArrayToolkit::column($joinLessonDetail, 'targetId');

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $userIds = ArrayToolkit::column($joinLessonDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $joinLessonStartData = $this->getOrderService()->searchOrders(array("status" => "paid"), "early", 0, 1);

        foreach ($joinLessonStartData as $key) {
            $joinLessonStartDate = date("Y-m-d", $key['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/join-lesson.html.twig", array(
            'JoinLessonDetail'    => $joinLessonDetail,
            'paginator'           => $paginator,
            'tab'                 => $tab,
            'data'                => $data,
            'courses'             => $courses,
            'users'               => $users,
            'joinLessonStartDate' => $joinLessonStartDate,
            'dataInfo'            => $dataInfo
        ));
    }

    public function exitLessonAction(Request $request, $tab)
    {
        $data                = array();
        $exitLessonStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders(array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "statusPaid" => "paid", "statusCreated" => "created")),
            20
        );

        $exitLessonDetail = $this->getOrderService()->searchOrders(
            array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "statusPaid" => "paid", "statusCreated" => "created"),
            "latest",
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $exitLessonData = "";

        if ($tab == "trend") {
            $exitLessonData = $this->getOrderService()->analysisExitCourseDataByTimeAndStatus($timeRange['startTime'], $timeRange['endTime']);

            $data = $this->fillAnalysisData($condition, $exitLessonData);
        }

        $courseIds = ArrayToolkit::column($exitLessonDetail, 'targetId');

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $userIds = ArrayToolkit::column($exitLessonDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $cancelledOrders = $this->getOrderService()->findRefundsByIds(ArrayToolkit::column($exitLessonDetail, 'refundId'));

        $cancelledOrders = ArrayToolkit::index($cancelledOrders, 'id');

        $exitLessonStartData = $this->getOrderService()->searchOrders(array("statusPaid" => "paid", "statusCreated" => "created"), "early", 0, 1);

        foreach ($exitLessonStartData as $key) {
            $exitLessonStartDate = date("Y-m-d", $key['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/exit-lesson.html.twig", array(
            'exitLessonDetail'    => $exitLessonDetail,
            'paginator'           => $paginator,
            'tab'                 => $tab,
            'data'                => $data,
            'courses'             => $courses,
            'users'               => $users,
            'exitLessonStartDate' => $exitLessonStartDate,
            'cancelledOrders'     => $cancelledOrders,
            'dataInfo'            => $dataInfo
        ));
    }

    public function paidCourseAction(Request $request, $tab)
    {
        $data                = array();
        $paidCourseStartDate = "";
        $count               = 0;

        $condition = $request->query->all();

        $timeRange = $this->getTimeRange($condition);

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders(array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "status" => "paid", "amount" => "0.00", "targetType" => 'course')),
            20
        );

        $paidCourseDetail = $this->getOrderService()->searchOrders(
            array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "status" => "paid", "amount" => "0.00", "targetType" => 'course'),
            "latest",
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $paidCourseData = "";

        if ($tab == "trend") {
            $paidCourseData = $this->getOrderService()->analysisPaidCourseOrderDataByTime($timeRange['startTime'], $timeRange['endTime']);
            $count          = count($paidCourseData);
            $data           = $this->fillAnalysisData($condition, $paidCourseData);
        }

        $courseIds = ArrayToolkit::column($paidCourseDetail, 'targetId'); //订单中的课程

        $courses = $this->getCourseService()->searchCourses( //订单中的课程zai剔除班级中的课程
            array('courseIds' => $courseIds, 'parentId' => '0'),
            "latest",
            0,
            count($paidCourseDetail)
        );
        $userIds = ArrayToolkit::column($paidCourseDetail, 'userId');
        $courses = ArrayToolkit::index($courses, 'id');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $paidCourseStartData = $this->getOrderService()->searchOrders(array("status" => "paid", "amount" => "0.00"), "early", 0, 1);

        foreach ($paidCourseStartData as $key) {
            $paidCourseStartDate = date("Y-m-d", $key['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render("admin/operation-analysis/paid-course.html.twig", array(
            'paidCourseDetail'    => $paidCourseDetail,
            'paginator'           => $paginator,
            'tab'                 => $tab,
            'data'                => $data,
            'courses'             => $courses,
            'users'               => $users,
            'paidCourseStartDate' => $paidCourseStartDate,
            'dataInfo'            => $dataInfo,
            'count'               => $count
        ));
    }

    public function paidClassroomAction(Request $request, $tab)
    {
        $data = array();

        $condition              = $request->query->all();
        $timeRange              = $this->getTimeRange($condition);
        $paidClassroomStartDate = '';

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders(array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "statusPaid" => "paid", "statusCreated" => "created", "targetType" => 'classroom')),
            20
        );
        $paidClassroomDetail = $this->getOrderService()->searchOrders(
            array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "status" => "paid", "amount" => "0.00", "targetType" => 'classroom'),
            "latest",
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $paidClassroomData = "";

        if ($tab == "trend") {
            $paidClassroomData = $this->getOrderService()->analysisPaidClassroomOrderDataByTime($timeRange['startTime'], $timeRange['endTime']);
            $data              = $this->fillAnalysisData($condition, $paidClassroomData);
        }

        $classroomIds = ArrayToolkit::column($paidClassroomDetail, 'targetId');

        $classroom = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        $userIds = ArrayToolkit::column($paidClassroomDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $paidClassroomStartData = $this->getOrderService()->searchOrders(array("status" => "paid", "amount" => "0.00"), "early", 0, 1);

        foreach ($paidClassroomStartData as $key) {
            $paidClassroomStartDate = date("Y-m-d", $key['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render("admin/operation-analysis/paid-classroom.html.twig", array(
            'paidClassroomDetail'    => $paidClassroomDetail,
            'paginator'              => $paginator,
            'tab'                    => $tab,
            'data'                   => $data,
            'classroom'              => $classroom,
            'users'                  => $users,
            'paidClassroomStartDate' => $paidClassroomStartDate,
            'dataInfo'               => $dataInfo
        ));
    }

    public function completedTaskAction(Request $request, $tab)
    {
        $data                   = array();
        $completedTaskStartDate = "";
        $count                  = 0;

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $paginator = new Paginator(
            $request,
            $this->getTaskResultService()->countTaskResults(array("startTime" => $timeRange['startTime'], "endTime" => $timeRange['endTime'], "status" => "finish")),
            20
        );

        $completedTaskDetail = $this->getTaskResultService()->searchTaskResults(
            array("startTime" => $timeRange['startTime'], "endTime" => $timeRange['endTime'], "status" => "finish"),
            array("finishedTime" => "DESC"),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $completedTaskData = "";

        if ($tab == "trend") {
            $completedTaskData = $this->getTaskResultService()->analysisCompletedTaskDataByTime($timeRange['startTime'], $timeRange['endTime']);
            $data              = $this->fillAnalysisData($condition, $completedTaskData);
            foreach ($completedTaskData as $val) {
                $count += $val['count'];
            }
        }

        $courseIds = ArrayToolkit::column($completedTaskDetail, 'courseId');

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $taskIds = ArrayToolkit::column($completedTaskDetail, 'courseTaskId');

        $tasks = ArrayToolkit::index($this->getTaskService()->findTasksByIds($taskIds), 'id');

        $courseSetIds = ArrayToolkit::column($courses, 'courseSetId');

        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);

        $userIds = ArrayToolkit::column($completedTaskDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $completedTaskStartData = $this->getTaskResultService()->searchTaskResults(array("status" => "finish"), array("finishedTime" => "ASC"), 0, 1);

        if ($completedTaskStartData) {
            $completedTaskStartDate = date("Y-m-d", $completedTaskStartData[0]['finishedTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render("admin/operation-analysis/completed-task.html.twig", array(
            'completedTaskDetail'    => $completedTaskDetail,
            'paginator'              => $paginator,
            'tab'                    => $tab,
            'data'                   => $data,
            'courseSets'             => $courseSets,
            'courses'                => $courses,
            'tasks'                  => $tasks,
            'users'                  => $users,
            'completedTaskStartDate' => $completedTaskStartDate,
            'dataInfo'               => $dataInfo,
            'count'                  => $count
        ));
    }

    public function videoViewedAction(Request $request, $tab)
    {
        $data      = array();
        $condition = $request->query->all();

        $timeRange = $this->getTimeRange($condition);

        $searchCondition = array(
            "fileType"  => 'video',
            "startTime" => $timeRange['startTime']
            , "endTime" => $timeRange['endTime']
        );

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->searchAnalysisLessonViewCount(
                $searchCondition,
                20
            )
        );

        $videoViewedDetail = $this->getCourseService()->searchAnalysisLessonView(
            $searchCondition,
            array("createdTime" => "DESC"),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $videoViewedTrendData = "";

        if ($tab == "trend") {
            $videoViewedTrendData = $this->getCourseService()->analysisLessonViewDataByTime($timeRange['startTime'], $timeRange['endTime'], array("fileType" => 'video'));

            $data = $this->fillAnalysisData($condition, $videoViewedTrendData);
        }

        $lessonIds = ArrayToolkit::column($videoViewedDetail, 'lessonId');
        $lessons   = $this->getCourseService()->findLessonsByIds($lessonIds);
        $lessons   = ArrayToolkit::index($lessons, 'id');

        $userIds = ArrayToolkit::column($videoViewedDetail, 'userId');
        $users   = $this->getUserService()->findUsersByIds($userIds);
        $users   = ArrayToolkit::index($users, 'id');

        $minCreatedTime = $this->getCourseService()->getAnalysisLessonMinTime('all');

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/video-view.html.twig", array(
            'videoViewedDetail' => $videoViewedDetail,
            'paginator'         => $paginator,
            'tab'               => $tab,
            'data'              => $data,
            'lessons'           => $lessons,
            'users'             => $users,
            'dataInfo'          => $dataInfo,
            'minCreatedTime'    => date("Y-m-d", $minCreatedTime['createdTime']),
            'showHelpMessage'   => 1
        ));
    }

    public function cloudVideoViewedAction(Request $request, $tab)
    {
        $data      = array();
        $condition = $request->query->all();

        $timeRange = $this->getTimeRange($condition);

        $searchCondition = array(
            "fileType"    => 'video',
            "fileStorage" => 'cloud',
            "startTime"   => $timeRange['startTime']
            , "endTime" => $timeRange['endTime']
        );

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->searchAnalysisLessonViewCount(
                $searchCondition,
                20
            )
        );

        $videoViewedDetail = $this->getCourseService()->searchAnalysisLessonView(
            $searchCondition,
            array("createdTime", "DESC"),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $videoViewedTrendData = "";

        if ($tab == "trend") {
            $videoViewedTrendData = $this->getCourseService()->analysisLessonViewDataByTime($timeRange['startTime'], $timeRange['endTime'], array("fileType" => 'video', "fileStorage" => 'cloud'));

            $data = $this->fillAnalysisData($condition, $videoViewedTrendData);
        }

        $lessonIds = ArrayToolkit::column($videoViewedDetail, 'lessonId');
        $lessons   = $this->getCourseService()->findLessonsByIds($lessonIds);
        $lessons   = ArrayToolkit::index($lessons, 'id');

        $userIds        = ArrayToolkit::column($videoViewedDetail, 'userId');
        $users          = $this->getUserService()->findUsersByIds($userIds);
        $users          = ArrayToolkit::index($users, 'id');
        $minCreatedTime = $this->getCourseService()->getAnalysisLessonMinTime('cloud');

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/cloud-video-view.html.twig", array(
            'videoViewedDetail' => $videoViewedDetail,
            'paginator'         => $paginator,
            'tab'               => $tab,
            'data'              => $data,
            'lessons'           => $lessons,
            'users'             => $users,
            'dataInfo'          => $dataInfo,
            'minCreatedTime'    => date("Y-m-d", $minCreatedTime['createdTime']),
            'showHelpMessage'   => 1
        ));
    }

    public function localVideoViewedAction(Request $request, $tab)
    {
        $data      = array();
        $condition = $request->query->all();

        $timeRange = $this->getTimeRange($condition);

        $searchCondition = array(
            "fileType"    => 'video',
            "fileStorage" => 'local',
            "startTime"   => $timeRange['startTime'],
            "endTime"     => $timeRange['endTime']
        );

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->searchAnalysisLessonViewCount(
                $searchCondition,
                20
            )
        );

        $videoViewedDetail = $this->getCourseService()->searchAnalysisLessonView(
            $searchCondition,
            array("createdTime" => "DESC"),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $videoViewedTrendData = "";

        if ($tab == "trend") {
            $videoViewedTrendData = $this->getCourseService()->analysisLessonViewDataByTime($timeRange['startTime'], $timeRange['endTime'], array("fileType" => 'video', "fileStorage" => 'local'));

            $data = $this->fillAnalysisData($condition, $videoViewedTrendData);
        }

        $lessonIds = ArrayToolkit::column($videoViewedDetail, 'lessonId');
        $lessons   = $this->getCourseService()->findLessonsByIds($lessonIds);
        $lessons   = ArrayToolkit::index($lessons, 'id');

        $userIds        = ArrayToolkit::column($videoViewedDetail, 'userId');
        $users          = $this->getUserService()->findUsersByIds($userIds);
        $users          = ArrayToolkit::index($users, 'id');
        $minCreatedTime = $this->getCourseService()->getAnalysisLessonMinTime('local');

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/local-video-view.html.twig", array(
            'videoViewedDetail' => $videoViewedDetail,
            'paginator'         => $paginator,
            'tab'               => $tab,
            'data'              => $data,
            'lessons'           => $lessons,
            'users'             => $users,
            'dataInfo'          => $dataInfo,
            'minCreatedTime'    => date("Y-m-d", $minCreatedTime['createdTime']),
            'showHelpMessage'   => 1
        ));
    }

    public function netVideoViewedAction(Request $request, $tab)
    {
        $data      = array();
        $condition = $request->query->all();

        $timeRange = $this->getTimeRange($condition);

        $searchCondition = array(
            "fileType"    => 'video',
            "fileStorage" => 'net',
            "startTime"   => $timeRange['startTime']
            , "endTime" => $timeRange['endTime']
        );

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->searchAnalysisLessonViewCount(
                $searchCondition,
                20
            )
        );

        $videoViewedDetail = $this->getCourseService()->searchAnalysisLessonView(
            $searchCondition,
            array("createdTime", "DESC"),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $videoViewedTrendData = "";

        if ($tab == "trend") {
            $videoViewedTrendData = $this->getCourseService()->analysisLessonViewDataByTime($timeRange['startTime'], $timeRange['endTime'], array("fileType" => 'video', "fileStorage" => 'net'));

            $data = $this->fillAnalysisData($condition, $videoViewedTrendData);
        }

        $lessonIds = ArrayToolkit::column($videoViewedDetail, 'lessonId');
        $lessons   = $this->getCourseService()->findLessonsByIds($lessonIds);
        $lessons   = ArrayToolkit::index($lessons, 'id');

        $userIds        = ArrayToolkit::column($videoViewedDetail, 'userId');
        $users          = $this->getUserService()->findUsersByIds($userIds);
        $users          = ArrayToolkit::index($users, 'id');
        $minCreatedTime = $this->getCourseService()->getAnalysisLessonMinTime('net');

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/net-video-view.html.twig", array(
            'videoViewedDetail' => $videoViewedDetail,
            'paginator'         => $paginator,
            'tab'               => $tab,
            'data'              => $data,
            'lessons'           => $lessons,
            'users'             => $users,
            'minCreatedTime'    => date("Y-m-d", $minCreatedTime['createdTime']),
            'dataInfo'          => $dataInfo,
            'showHelpMessage'   => 1
        ));
    }

    public function incomeAction(Request $request, $tab)
    {
        $data            = array();
        $incomeStartDate = "";
        $count           = 0;

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $incomeData = "";

        if ($tab == "trend") {
            $incomeData = $this->getOrderService()->analysisAmountDataByTime($timeRange['startTime'], $timeRange['endTime']);
            $data       = $this->fillAnalysisData($condition, $incomeData);
            foreach ($incomeData as $val) {
                $count += $val['count'];
            }
        }

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders(array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "status" => "paid", "amount" => "0.00")),
            20
        );

        $incomeDetail = $this->getOrderService()->searchOrders(
            array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "status" => "paid", "amount" => "0.00"),
            "latest",
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $incomeDetailByGroup = ArrayToolkit::group($incomeDetail, 'targetType');

        $courses = array();

        if (isset($incomeDetailByGroup['course'])) {
            $courseIds = ArrayToolkit::column($incomeDetailByGroup['course'], 'targetId');
            $courses   = $this->getCourseService()->findCoursesByIds($courseIds);
        }

        $classrooms = array();

        if (isset($incomeDetailByGroup['classroom'])) {
            $classroomIds = ArrayToolkit::column($incomeDetailByGroup['classroom'], 'targetId');
            $classrooms   = $this->getClassroomService()->findClassroomsByIds($classroomIds);
        }

        $userIds = ArrayToolkit::column($incomeDetail, 'userId');
        $users   = $this->getUserService()->findUsersByIds($userIds);

        $incomeStartData = $this->getOrderService()->searchOrders(array("status" => "paid", "amount" => "0.00"), "early", 0, 1);

        foreach ($incomeStartData as $key) {
            $incomeStartDate = date("Y-m-d", $key['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/income.html.twig", array(
            'incomeDetail'    => $incomeDetail,
            'paginator'       => $paginator,
            'tab'             => $tab,
            'data'            => $data,
            'courses'         => $courses,
            'classrooms'      => $classrooms,
            'users'           => $users,
            'incomeStartDate' => $incomeStartDate,
            'dataInfo'        => $dataInfo,
            'count'           => $count
        ));
    }

    public function courseSetIncomeAction(Request $request, $tab)
    {
        $data                     = array();
        $courseSetIncomeStartDate = "";
        $count                    = 0;

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders(array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "status" => "paid", "targetType" => "course", "amount" => "0.00")),
            20
        );

        $courseIncomeDetail = $this->getOrderService()->searchOrders(
            array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "status" => "paid", "targetType" => "course", "amount" => '0.00'),
            "latest",
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseIncomeData = "";

        if ($tab == "trend") {
            $courseIncomeData = $this->getOrderService()->analysisCourseAmountDataByTime($timeRange['startTime'], $timeRange['endTime']);
            $data             = $this->fillAnalysisData($condition, $courseIncomeData);
            foreach ($courseIncomeData as $val) {
                $count += $val['count'];
            }
        }

        $courseIds = ArrayToolkit::column($courseIncomeDetail, 'targetId');

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $userIds = ArrayToolkit::column($courseIncomeDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $courseIncomeStartData = $this->getOrderService()->searchOrders(array("status" => "paid", "amount" => "0.00", "targetType" => "course"), "early", 0, 1);

        foreach ($courseIncomeStartData as $key) {
            $courseIncomeStartDate = date("Y-m-d", $key['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render("admin/operation-analysis/courseSetIncome.html.twig", array(
            'courseIncomeDetail'    => $courseIncomeDetail,
            'paginator'             => $paginator,
            'tab'                   => $tab,
            'data'                  => $data,
            'courses'               => $courses,
            'users'                 => $users,
            'courseIncomeStartDate' => $courseIncomeStartDate,
            'dataInfo'              => $dataInfo,
            'count'                 => $count
        ));
    }

    public function classroomIncomeAction(Request $request, $tab)
    {
        $data                     = array();
        $classroomIncomeStartDate = "";
        $count                    = 0;

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders(array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "status" => "paid", "targetType" => "classroom", "amount" => "0.00")),
            20
        );

        $classroomIncomeDetail = $this->getOrderService()->searchOrders(
            array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "status" => "paid", "targetType" => "classroom", "amount" => '0.00'),
            "latest",
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $classroomIncomeData = "";

        if ($tab == "trend") {
            $classroomIncomeData = $this->getOrderService()->analysisClassroomAmountDataByTime($timeRange['startTime'], $timeRange['endTime']);
            $data                = $this->fillAnalysisData($condition, $classroomIncomeData);
            foreach ($classroomIncomeData as $val) {
                $count += $val['count'];
            }
        }

        $classroomIds = ArrayToolkit::column($classroomIncomeDetail, 'targetId');

        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        $userIds = ArrayToolkit::column($classroomIncomeDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $classroomIncomeStartData = $this->getOrderService()->searchOrders(array("status" => "paid", "amount" => "0.00", "targetType" => "classroom"), "early", 0, 1);

        foreach ($classroomIncomeStartData as $key) {
            $classroomIncomeStartDate = date("Y-m-d", $key['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/classroomIncome.html.twig", array(
            'classroomIncomeDetail'    => $classroomIncomeDetail,
            'paginator'                => $paginator,
            'tab'                      => $tab,
            'data'                     => $data,
            'classrooms'               => $classrooms,
            'users'                    => $users,
            'classroomIncomeStartDate' => $classroomIncomeStartDate,
            'dataInfo'                 => $dataInfo,
            'count'                    => $count
        ));
    }

    public function vipIncomeAction(Request $request, $tab)
    {
        $data               = array();
        $vipIncomeStartDate = "";
        $count              = 0;

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders(array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "status" => "paid", "targetType" => "vip", "amount" => "0.00")),
            20
        );

        $vipIncomeDetail = $this->getOrderService()->searchOrders(
            array("paidStartTime" => $timeRange['startTime'], "paidEndTime" => $timeRange['endTime'], "status" => "paid", "targetType" => "vip", "amount" => '0.00'),
            "latest",
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $vipIncomeData = "";

        if ($tab == "trend") {
            $vipIncomeData = $this->getOrderService()->analysisvipAmountDataByTime($timeRange['startTime'], $timeRange['endTime']);
            $data          = $this->fillAnalysisData($condition, $vipIncomeData);
            foreach ($vipIncomeData as $val) {
                $count += $val['count'];
            }
        }

        $userIds = ArrayToolkit::column($vipIncomeDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $vipIncomeStartData = $this->getOrderService()->searchOrders(array("status" => "paid", "amount" => "0.00", "targetType" => "vip"), "early", 0, 1);

        foreach ($vipIncomeStartData as $key) {
            $vipIncomeStartDate = date("Y-m-d", $key['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/vipIncome.html.twig", array(
            'vipIncomeDetail'    => $vipIncomeDetail,
            'paginator'          => $paginator,
            'tab'                => $tab,
            'data'               => $data,
            'users'              => $users,
            'vipIncomeStartDate' => $vipIncomeStartDate,
            'dataInfo'           => $dataInfo,
            'count'              => $count
        ));
    }

    protected function fillAnalysisSum($condition, $currentData, $initValue = 0)
    {
        $timeRange = $this->getTimeRange($condition);
        $dateRange = DateToolkit::generateDateRange(
            date('Y-m-d', $timeRange['startTime']),
            date('Y-m-d', $timeRange['endTime'])
        );

        $initData = array();

        foreach ($dateRange as $value) {
            $initData[] = array('date' => $value, 'count' => $initValue);
        }

        for ($i = 0; $i < count($initData); $i++) {
            foreach ($currentData as $value) {
                if (in_array($initData[$i]['date'], $value)) {
                    $initData[$i]['count'] += $value['count'];
                    break;
                }
            }
            if (isset($initData[$i + 1])) {
                $initData[$i + 1]['count'] = $initData[$i]['count'];
            }
        }

        return json_encode($initData);
    }

    protected function fillAnalysisData($condition, $currentData)
    {
        $timeRange = $this->getTimeRange($condition);
        $dateRange = DateToolkit::generateDateRange(
            date('Y-m-d', $timeRange['startTime']),
            date('Y-m-d', $timeRange['endTime'])
        );

        foreach ($dateRange as $key => $value) {
            $zeroData[] = array("date" => $value, "count" => 0);
        }

        $currentData = ArrayToolkit::index($currentData, 'date');

        $zeroData = ArrayToolkit::index($zeroData, 'date');

        $currentData = array_merge($zeroData, $currentData);

        $currentData = array_values($currentData);

        return json_encode($currentData);
    }

    protected function getDataInfo($condition, $timeRange)
    {
        return array(
            'startTime'            => date("Y-m-d", $timeRange['startTime']),
            'endTime'              => date("Y-m-d", $timeRange['endTime'] - 24 * 3600),
            'currentMonthStart'    => date("Y-m-d", strtotime(date("Y-m", time()))),
            'currentMonthEnd'      => date("Y-m-d", strtotime(date("Y-m-d", time()))),
            'lastMonthStart'       => date("Y-m-d", strtotime(date("Y-m", strtotime("-1 month")))),
            'lastMonthEnd'         => date("Y-m-d", strtotime(date("Y-m", time())) - 24 * 3600),
            'lastThreeMonthsStart' => date("Y-m-d", strtotime(date("Y-m", strtotime("-2 month")))),
            'lastThreeMonthsEnd'   => date("Y-m-d", strtotime(date("Y-m-d", time()))),
            'analysisDateType'     => $condition["analysisDateType"]);
    }

    protected function getTimeRange($fields)
    {
        $startTime = !empty($fields['startTime']) ? $fields['startTime'] : date("Y-m", time());
        $endTime   = !empty($fields['endTime']) ? $fields['endTime'] : date("Y-m", time());

        return array(
            'startTime' => strtotime($startTime),
            'endTime'   => strtotime($endTime) + 24 * 3600
        );
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
