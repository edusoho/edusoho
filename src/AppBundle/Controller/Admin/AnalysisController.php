<?php
namespace AppBundle\Controller\Admin;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class AnalysisController extends BaseController
{
    public function rountByanalysisDateTypeAction(Request $request, $tab)
    {
        $analysisDateType = $request->query->get("analysisDateType");
        return $this->forward('AppBundle:Admin/Analysis:'.$analysisDateType, array(
            'request' => $request,
            'tab'     => $tab
        ));
    }

    public function registerAction(Request $request, $tab)
    {
        $data              = array();
        $count = 0;
        $registerStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        if (!$timeRange) {
            $this->setFlashMessage("danger", $this->getServiceKernel()->trans('输入的日期有误!'));
            return $this->redirect($this->generateUrl('admin_operation_analysis_register', array(
                'tab' => "trend"
            )));
        }

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

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_user_sum', array(
                'tab' => "trend"
            )));
        }

        $result = array(
            'tab' => $tab
        );

        if ($tab == "trend") {
            $userSumData    = $this->getUserService()->analysisUserSumByTime($timeRange['endTime']);
            $data           = $this->fillAnalysisUserSum($condition, $userSumData);
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
        $data               = array();
        $courseSetSumStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $count = $this->getCourseSetService()->countCourseSets($timeRange);

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_course_set_sum', array(
                'tab' => "trend"
            )));
        }

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

        $courseSetSumData = "";

        if ($tab == "trend") {
            $courseSetSumData = $this->getCourseSetService()->analysisCourseSetSumByTime($timeRange['endTime']);

            $data = $this->fillAnalysisCourseSum($condition, $courseSetSumData);
        }

        $userIds = ArrayToolkit::column($courseSetSumDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSetSumDetail, 'categoryId'));

        $courseSetSumStartData = $this->getCourseSetService()->searchCourseSets(array(), array('createdTime' => 'ASC'), 0, 1);

        if ($courseSetSumStartData) {
            $courseSetSumStartDate = date("Y-m-d", $courseSetSumStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/course-sum.html.twig", array(
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

    public function loginAction(Request $request, $tab)
    {
        $data           = array();
        $loginStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $count = 0;

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_login', array(
                'tab' => "trend"
            )));
        }

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
            $data = $this->fillAnalysisData($condition, $loginData);
            $count = $this->getLogService()->analysisLoginNumByTime($timeRange['startTime'], $timeRange['endTime']);
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
            'count'          => $count,
        ));
    }

    public function courseSetAction(Request $request, $tab)
    {
        $data            = array();
        $courseSetStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $count = $this->getCourseSetService()->countCourseSets($timeRange);

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_course_set', array(
                'tab' => "trend"
            )));
        }

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
            'paginator'       => $paginator,
            'tab'             => $tab,
            'categories'      => $categories,
            'data'            => $data,
            'users'           => $users,
            'courseSetStartDate' => $courseSetStartDate,
            'dataInfo'        => $dataInfo,
            'count'           => $count,
        ));
    }

    public function lessonAction(Request $request, $tab)
    {
        $data            = array();
        $lessonStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_lesson', array(
                'tab' => "trend"
            )));
        }

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

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_lesson_join', array(
                'tab' => "trend"
            )));
        }

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

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_lesson_exit', array(
                'tab' => "trend"
            )));
        }

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

    public function paidLessonAction(Request $request, $tab)
    {
        $data                = array();
        $paidLessonStartDate = "";

        $condition = $request->query->all();

        $timeRange = $this->getTimeRange($condition);

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_lesson_paid', array(
                'tab' => "trend"
            )));
        }

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

        $paidLessonData = "";

        if ($tab == "trend") {
            $paidLessonData = $this->getOrderService()->analysisPaidCourseOrderDataByTime($timeRange['startTime'], $timeRange['endTime']);

            $data = $this->fillAnalysisData($condition, $paidLessonData);
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

        $paidLessonStartData = $this->getOrderService()->searchOrders(array("status" => "paid", "amount" => "0.00"), "early", 0, 1);

        foreach ($paidLessonStartData as $key) {
            $paidLessonStartDate = date("Y-m-d", $key['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render("admin/operation-analysis/paid-lesson.html.twig", array(
            'paidCourseDetail'    => $paidCourseDetail,
            'paginator'           => $paginator,
            'tab'                 => $tab,
            'data'                => $data,
            'courses'             => $courses,
            'users'               => $users,
            'paidLessonStartDate' => $paidLessonStartDate,
            'dataInfo'            => $dataInfo
        ));
    }

    public function paidClassroomAction(Request $request, $tab)
    {
        $data = array();

        $condition              = $request->query->all();
        $timeRange              = $this->getTimeRange($condition);
        $paidClassroomStartDate = '';

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_Classroom_paid', array(
                'tab' => "trend"
            )));
        }

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

    public function finishedLessonAction(Request $request, $tab)
    {
        $data                    = array();
        $finishedLessonStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_lesson_finished', array(
                'tab' => "trend"
            )));
        }

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->searchLearnCount(array("startTime" => $timeRange['startTime'], "endTime" => $timeRange['endTime'], "status" => "finished")),
            20
        );

        $finishedLessonDetail = $this->getCourseService()->searchLearns(
            array("startTime" => $timeRange['startTime'], "endTime" => $timeRange['endTime'], "status" => "finished"),
            array("finishedTime", "DESC"),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $finishedLessonData = "";

        if ($tab == "trend") {
            $finishedLessonData = $this->getCourseService()->analysisLessonFinishedDataByTime($timeRange['startTime'], $timeRange['endTime']);

            $data = $this->fillAnalysisData($condition, $finishedLessonData);
        }

        $courseIds = ArrayToolkit::column($finishedLessonDetail, 'courseId');

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $lessonIds = ArrayToolkit::column($finishedLessonDetail, 'lessonId');

        $lessons = $this->getCourseService()->findLessonsByIds($lessonIds);

        $userIds = ArrayToolkit::column($finishedLessonDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $finishedLessonStartData = $this->getCourseService()->searchLearns(array("status" => "finished"), array("finishedTime", "ASC"), 0, 1);

        if ($finishedLessonStartData) {
            $finishedLessonStartDate = date("Y-m-d", $finishedLessonStartData[0]['finishedTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        return $this->render("admin/operation-analysis/finished-lesson.html.twig", array(
            'finishedLessonDetail'    => $finishedLessonDetail,
            'paginator'               => $paginator,
            'tab'                     => $tab,
            'data'                    => $data,
            'courses'                 => $courses,
            'lessons'                 => $lessons,
            'users'                   => $users,
            'finishedLessonStartDate' => $finishedLessonStartDate,
            'dataInfo'                => $dataInfo
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

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_video_viewed', array(
                'tab' => "trend"
            )));
        }

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

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_video_viewed', array(
                'tab' => "trend"
            )));
        }

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
            "endTime" => $timeRange['endTime']
        );

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_video_viewed', array(
                'tab' => "trend"
            )));
        }

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

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_video_viewed', array(
                'tab' => "trend"
            )));
        }

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

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_income', array(
                'tab' => "trend"
            )));
        }

        $incomeData = "";

        if ($tab == "trend") {
            $incomeData = $this->getOrderService()->analysisAmountDataByTime($timeRange['startTime'], $timeRange['endTime']);
            $data       = $this->fillAnalysisData($condition, $incomeData);
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
            'dataInfo'        => $dataInfo
        ));
    }

    public function courseIncomeAction(Request $request, $tab)
    {
        $data                  = array();
        $courseIncomeStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_course_income', array(
                'tab' => "trend"
            )));
        }

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

            $data = $this->fillAnalysisData($condition, $courseIncomeData);
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
        return $this->render("admin/operation-analysis/courseIncome.html.twig", array(
            'courseIncomeDetail'    => $courseIncomeDetail,
            'paginator'             => $paginator,
            'tab'                   => $tab,
            'data'                  => $data,
            'courses'               => $courses,
            'users'                 => $users,
            'courseIncomeStartDate' => $courseIncomeStartDate,
            'dataInfo'              => $dataInfo
        ));
    }

    public function classroomIncomeAction(Request $request, $tab)
    {
        $data                     = array();
        $classroomIncomeStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_classroom_income', array(
                'tab' => "trend"
            )));
        }

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

            $data = $this->fillAnalysisData($condition, $classroomIncomeData);
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
            'dataInfo'                 => $dataInfo
        ));
    }

    public function vipIncomeAction(Request $request, $tab)
    {
        $data               = array();
        $vipIncomeStartDate = "";

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        if (!$timeRange) {
            $this->setFlashMessage("danger", '输入的日期有误!');
            return $this->redirect($this->generateUrl('admin_operation_analysis_vip_income', array(
                'tab' => "trend"
            )));
        }

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

            $data = $this->fillAnalysisData($condition, $vipIncomeData);
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
            'dataInfo'           => $dataInfo
        ));
    }

    protected function fillAnalysisUserSum($condition, $currentData)
    {
        $dates       = $this->getDatesByCondition($condition);
        $currentData = ArrayToolkit::index($currentData, 'date');
        $timeRange   = $this->getTimeRange($condition);
        $userSumData = array();

        foreach ($dates as $key => $value) {
            $zeroData[] = array("date" => $value, "count" => 0);
        }

        $userSumData = $this->getUserService()->analysisUserSumByTime($timeRange['endTime']);

        if ($userSumData) {
            $countTmp = $userSumData[0]["count"];

            foreach ($zeroData as $key => $value) {
                foreach ($userSumData as $userKey => $val) {
                    if ($userKey != 0 && ($value['date'] < $val['date']) && (isset($userSumData[($userKey + 1)]) && $value['date'] > $userSumData[($userKey + 1)]['date'])) {
                        $countTmp = $userSumData[($userKey + 1)]['count'];
                    }
                }

                $date = $value['date'];

                if (array_key_exists($date, $currentData)) {
                    $zeroData[$key]['count'] = $currentData[$date]['count'];
                    $countTmp                = $currentData[$date]['count'];
                } else {
                    $zeroData[$key]['count'] = $countTmp;
                }
            }
        }

        return json_encode($zeroData);
    }

    protected function fillAnalysisCourseSum($condition, $currentData)
    {
        $dates       = $this->getDatesByCondition($condition);
        $currentData = ArrayToolkit::index($currentData, 'date');
        $timeRange   = $this->getTimeRange($condition);
        $zeroData    = array();

        foreach ($dates as $key => $value) {
            $zeroData[] = array("date" => $value, "count" => 0);
        }

        $courseSumData = $this->getCourseService()->analysisCourseSumByTime($timeRange['endTime']);

        if ($courseSumData) {
            $countTmp = $courseSumData[0]["count"];

            foreach ($zeroData as $key => $value) {
                if ($value["date"] < $courseSumData[0]["date"]) {
                    $countTmp = 0;
                } else {
                    foreach ($courseSumData as $courseKey => $val) {
                        if ($courseKey != 0 && ($value['date'] < $val['date']) && ($value['date'] > $courseSumData[($courseKey - 1)]['date'])) {
                            $countTmp = $courseSumData[($courseKey - 1)]['count'];
                        }
                    }
                }

                $date = $value['date'];

                if (array_key_exists($date, $currentData)) {
                    $zeroData[$key]['count'] = $currentData[$date]['count'];
                    $countTmp                = $currentData[$date]['count'];
                } else {
                    $zeroData[$key]['count'] = $countTmp;
                }
            }
        }

        return json_encode($zeroData);
    }

    protected function fillAnalysisData($condition, $currentData)
    {
        $dates = $this->getDatesByCondition($condition);

        foreach ($dates as $key => $value) {
            $zeroData[] = array("date" => $value, "count" => 0);
        }

        $currentData = ArrayToolkit::index($currentData, 'date');

        $zeroData = ArrayToolkit::index($zeroData, 'date');

        $currentData = array_merge($zeroData, $currentData);

        foreach ($currentData as $key => $value) {
            $data[] = $value;
        }

        return json_encode($data);
    }

    protected function getDatesByCondition($condition)
    {
        $timeRange = $this->getTimeRange($condition);

        $dates = $this->makeDateRange($timeRange['startTime'], $timeRange['endTime'] - 24 * 3600);

        return $dates;
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
        if (isset($fields['startTime']) && isset($fields['endTime']) && $fields['startTime'] != "" && $fields['endTime'] != "") {
            if ($fields['startTime'] > $fields['endTime']) {
                return false;
            }

            return array('startTime' => strtotime($fields['startTime']), 'endTime' => (strtotime($fields['endTime']) + 24 * 3600));
        }

        return array('startTime' => strtotime(date("Y-m", time())), 'endTime' => strtotime(date("Y-m-d", time() + 24 * 3600)));
    }

    protected function makeDateRange($startTime, $endTime)
    {
        $dates = array();

        $currentTime = $startTime;

        while (true) {
            if ($currentTime > $endTime) {
                break;
            }

            $currentDate = date('Y-m-d', $currentTime);
            $dates[]     = $currentDate;

            $currentTime = $currentTime + 3600 * 24;
        }

        return $dates;
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
