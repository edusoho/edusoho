<?php

namespace AppBundle\Controller\AdminV2\DataStatistics;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\DateToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;
use AppBundle\Common\MathToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\MemberOperation\Service\MemberOperationService;
use Biz\System\Service\LogService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Task\Service\ViewLogService;
use Biz\Taxonomy\Service\CategoryService;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\Biz\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;

class StatisticsController extends BaseController
{
    public function indexAction(Request $request, $tab)
    {
        $analysisDateType = $request->query->get('analysisDateType', '');
        if (!$analysisDateType) {
            throw new InvalidArgumentException('analysisDateType missing');
        }

        return $this->forward(
            "AppBundle:AdminV2/DataStatistics/Statistics:{$analysisDateType}",
            array(
                'request' => $request,
                'tab' => $tab,
            )
        );
    }

    public function registerAction(Request $request, $tab)
    {
        $data = array();
        $count = 0;
        $registerStartDate = '';

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);
        $paginator = new Paginator(
            $request,
            $this->getUserService()->countUsers($timeRange),
            20
        );

        $registerDetail = $this->getUserService()->searchUsers(
            $timeRange,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if ('trend' == $tab) {
            $registerData = $this->getUserService()->analysisRegisterDataByTime(
                $timeRange['startTime'],
                $timeRange['endTime']
            );
            $data = $this->fillAnalysisData($condition, $registerData);
            foreach ($registerData as $key => $value) {
                $count += $value['count'];
            }
        }

        $registerStartData = $this->getUserService()->searchUsers(array(), array('createdTime' => 'ASC'), 0, 1);

        if ($registerStartData) {
            $registerStartDate = date('Y-m-d', $registerStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        $registerIds = ArrayToolkit::column($registerDetail, 'id');
        $registerProfiles = $this->getUserService()->findUserProfilesByIds($registerIds);

        return $this->render(
            'admin-v2/data-statistics/statistics/register.html.twig',
            array(
                'registerDetail' => $registerDetail,
                'paginator' => $paginator,
                'tab' => $tab,
                'registerProfiles' => $registerProfiles,
                'data' => $data,
                'registerStartDate' => $registerStartDate,
                'dataInfo' => $dataInfo,
                'count' => $count,
            )
        );
    }

    public function userSumAction(Request $request, $tab)
    {
        $data = array();
        $userSumStartDate = '';
        $userSumDetail = array();

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $result = array(
            'tab' => $tab,
        );

        if ('trend' == $tab) {
            $registerData = $this->getUserService()->analysisRegisterDataByTime(
                $timeRange['startTime'],
                $timeRange['endTime']
            );
            $userInitCount = $this->getUserService()->countUsers(
                array('endTime' => $timeRange['startTime'])
            );
            $data = $this->fillAnalysisSum($condition, $registerData, $userInitCount);
            $result['data'] = $data;
        } else {
            $paginator = new Paginator(
                $request,
                $this->getUserService()->countUsers($timeRange),
                20
            );

            $userSumDetail = $this->getUserService()->searchUsers(
                $timeRange,
                array('createdTime' => 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
            $result['userSumDetail'] = $userSumDetail;
            $result['paginator'] = $paginator;
        }

        $userSumStartData = $this->getUserService()->searchUsers(array(), array('createdTime' => 'ASC'), 0, 1);

        if ($userSumStartData) {
            $userSumStartDate = date('Y-m-d', $userSumStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        $result['userSumStartDate'] = $userSumStartDate;
        $result['dataInfo'] = $dataInfo;

        $userSumIds = ArrayToolkit::column($userSumDetail, 'id');
        $userSumProfiles = $this->getUserService()->findUserProfilesByIds($userSumIds);
        $result['userSumProfiles'] = $userSumProfiles;

        return $this->render('admin-v2/data-statistics/statistics/user-sum.html.twig', $result);
    }

    public function courseSetSumAction(Request $request, $tab)
    {
        $data = array();
        $courseSetSumStartDate = '';

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $courseSetConditions = $timeRange;
        $courseSetConditions['parentId'] = 0;

        $count = $this->getCourseSetService()->countCourseSets($courseSetConditions);
        $paginator = new Paginator(
            $request,
            $count,
            20
        );

        $courseSetSumDetail = $this->getCourseSetService()->searchCourseSets(
            $courseSetConditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseSetInitSum = '';

        if ('trend' == $tab) {
            $courseSetData = $this->getCourseSetService()->analysisCourseSetDataByTime(
                $timeRange['startTime'],
                $timeRange['endTime']
            );
            $courseSetInitSum = $this->getCourseSetService()->countCourseSets(
                array('endTime' => $timeRange['startTime'])
            );
            $data = $this->fillAnalysisSum($condition, $courseSetData, $courseSetInitSum);
        }

        $userIds = ArrayToolkit::column($courseSetSumDetail, 'creator');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $categories = $this->getCategoryService()->findCategoriesByIds(
            ArrayToolkit::column($courseSetSumDetail, 'categoryId')
        );

        $courseSetSumStartData = $this->getCourseSetService()->searchCourseSets(
            array(),
            array('createdTime' => 'ASC'),
            0,
            1
        );

        if ($courseSetSumStartData) {
            $courseSetSumStartDate = date('Y-m-d', $courseSetSumStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render(
            'admin-v2/data-statistics/statistics/course-set-sum.html.twig',
            array(
                'courseSetSumDetail' => $courseSetSumDetail,
                'paginator' => $paginator,
                'tab' => $tab,
                'categories' => $categories,
                'data' => $data,
                'users' => $users,
                'courseSetSumStartDate' => $courseSetSumStartDate,
                'dataInfo' => $dataInfo,
            )
        );
    }

    public function courseSumAction(Request $request, $tab)
    {
        $data = array();
        $courseSumStartDate = '';

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $courseConditions = $timeRange;
        $courseConditions['parentId'] = 0;

        $count = $this->getCourseService()->countCourses($courseConditions);

        $paginator = new Paginator(
            $request,
            $count,
            20
        );

        $courseSumDetail = $this->getCourseService()->searchCourses(
            $courseConditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if ('trend' == $tab) {
            $courseData = $this->getCourseService()->analysisCourseDataByTime(
                $timeRange['startTime'],
                $timeRange['endTime']
            );
            $courseInitSum = $this->getCourseService()->countCourses(array('endTime' => $timeRange['startTime']));
            $data = $this->fillAnalysisSum($condition, $courseData, $courseInitSum);
        }

        $userIds = ArrayToolkit::column($courseSumDetail, 'creator');
        $courseSets = $this->getCourseSetService()->findCourseSetsByCourseIds(ArrayToolkit::column($courseSumDetail, 'id'));
        $courseSets = ArrayToolkit::index($courseSets, 'id');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $categories = $this->getCategoryService()->findCategoriesByIds(
            ArrayToolkit::column($courseSumDetail, 'categoryId')
        );

        $courseStartData = $this->getCourseService()->searchCourses(array(), array('createdTime' => 'ASC'), 0, 1);

        if ($courseStartData) {
            $courseStartDate = date('Y-m-d', $courseStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render(
            'admin-v2/data-statistics/statistics/course-sum.html.twig',
            array(
                'courseSumDetail' => $courseSumDetail,
                'paginator' => $paginator,
                'tab' => $tab,
                'categories' => $categories,
                'courseSets' => $courseSets,
                'data' => $data,
                'users' => $users,
                'courseSumStartDate' => $courseSumStartDate,
                'dataInfo' => $dataInfo,
                'count' => count($courseSumDetail),
            )
        );
    }

    public function loginAction(Request $request, $tab)
    {
        $data = array();
        $loginStartDate = '';

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $count = 0;

        $paginator = new Paginator(
            $request,
            $this->getLogService()->searchLogCount(
                array(
                    'action' => 'login_success',
                    'startDateTime' => date('Y-m-d H:i:s', $timeRange['startTime']),
                    'endDateTime' => date('Y-m-d H:i:s', $timeRange['endTime']),
                )
            ),
            20
        );

        $loginDetail = $this->getLogService()->searchLogs(
            array(
                'action' => 'login_success',
                'startDateTime' => date('Y-m-d H:i:s', $timeRange['startTime']),
                'endDateTime' => date('Y-m-d H:i:s', $timeRange['endTime']),
            ),
            'created',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $loginData = '';

        if ('trend' == $tab) {
            $loginData = $this->getLogService()->analysisLoginDataByTime(
                $timeRange['startTime'],
                $timeRange['endTime']
            );
            $data = $this->fillAnalysisData($condition, $loginData);
            $count = $this->getLogService()->analysisLoginNumByTime($timeRange['startTime'], $timeRange['endTime']);
        }

        $userIds = ArrayToolkit::column($loginDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $loginStartData = $this->getLogService()->searchLogs(array('action' => 'login_success'), 'createdByAsc', 0, 1);

        if ($loginStartData) {
            $loginStartDate = date('Y-m-d', $loginStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render(
            'admin-v2/data-statistics/statistics/login.html.twig',
            array(
                'loginDetail' => $loginDetail,
                'paginator' => $paginator,
                'tab' => $tab,
                'data' => $data,
                'users' => $users,
                'loginStartDate' => $loginStartDate,
                'dataInfo' => $dataInfo,
                'count' => $count,
            )
        );
    }

    public function courseSetAction(Request $request, $tab)
    {
        $data = array();
        $courseSetStartDate = '';

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);
        $courseSetconditions = $timeRange;
        $courseSetConditions['parentId'] = 0;

        $count = $this->getCourseSetService()->countCourseSets($courseSetConditions);

        $paginator = new Paginator(
            $request,
            $count,
            20
        );

        $courseSetDetail = $this->getCourseSetService()->searchCourseSets(
            $courseSetConditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseSetData = '';

        if ('trend' == $tab) {
            $courseSetData = $this->getCourseSetService()->analysisCourseSetDataByTime(
                $timeRange['startTime'],
                $timeRange['endTime']
            );

            $data = $this->fillAnalysisData($condition, $courseSetData);
        }

        $userIds = ArrayToolkit::column($courseSetDetail, 'creator');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $categories = $this->getCategoryService()->findCategoriesByIds(
            ArrayToolkit::column($courseSetDetail, 'categoryId')
        );

        $courseSetStartData = $this->getCourseSetService()->searchCourseSets(array(), 'createdTimeByAsc', 0, 1);

        if ($courseSetStartData) {
            $courseSetStartDate = date('Y-m-d', $courseSetStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render(
            'admin-v2/data-statistics/statistics/course-set.html.twig',
            array(
                'courseSetDetail' => $courseSetDetail,
                'paginator' => $paginator,
                'tab' => $tab,
                'categories' => $categories,
                'data' => $data,
                'users' => $users,
                'courseSetStartDate' => $courseSetStartDate,
                'dataInfo' => $dataInfo,
                'count' => $count,
            )
        );
    }

    public function taskAction(Request $request, $tab)
    {
        $data = array();
        $taskStartDate = '';

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $taskDetailConditions = array(
            'createdTime_GE' => $timeRange['startTime'],
            'createdTime_LT' => $timeRange['endTime'],
        );

        $paginator = new Paginator(
            $request,
            $this->getTaskService()->countTasks($taskDetailConditions),
            20
        );

        $taskDetail = $this->getTaskService()->searchTasks(
            $taskDetailConditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $count = 0;
        if ('trend' == $tab) {
            $taskData = $this->getTaskService()->analysisTaskDataByTime($timeRange['startTime'], $timeRange['endTime']);
            $data = $this->fillAnalysisData($condition, $taskData);
            $count = $this->sumTrendDataCount($taskData);
        }

        $courseIds = ArrayToolkit::column($taskDetail, 'courseId');

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $courseSetIds = ArrayToolkit::column($courses, 'courseSetId');

        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);

        $userIds = ArrayToolkit::column($courses, 'creator');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $taskStartData = $this->getTaskService()->searchTasks(
            array(),
            array('createdTime' => 'ASC'),
            0,
            1
        );

        if ($taskStartData) {
            $taskStartDate = date('Y-m-d', $taskStartData[0]['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render(
            'admin-v2/data-statistics/statistics/task.html.twig',
            array(
                'taskDetail' => $taskDetail,
                'paginator' => $paginator,
                'tab' => $tab,
                'data' => $data,
                'courses' => $courses,
                'courseSets' => $courseSets,
                'users' => $users,
                'taskStartDate' => $taskStartDate,
                'dataInfo' => $dataInfo,
                'count' => $count,
            )
        );
    }

    public function joinLessonAction(Request $request, $tab)
    {
        $data = array();
        $joinLessonStartDate = '';

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $detailConditions = array(
            'operate_time_GT' => $timeRange['startTime'],
            'operate_time_LT' => $timeRange['endTime'],
            'operate_type' => 'join',
            'target_type' => 'course',
        );
        $count = $this->getMemberOperationService()->countRecords($detailConditions);
        $paginator = new Paginator(
            $request,
            $count,
            20
        );

        $joinLessonDetail = $this->getMemberOperationService()->searchRecords(
            $detailConditions,
            array('operate_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $joinLessonData = '';

        if ('trend' == $tab) {
            $joinLessonData = $this->getMemberOperationService()->countGroupByDate(
                $detailConditions,
                'ASC'
            );
            $data = $this->fillAnalysisData($condition, $joinLessonData);
            $this->sumTrendDataCount($joinLessonData);
        }

        $courseIds = ArrayToolkit::column($joinLessonDetail, 'target_id');

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courseSetIds = ArrayToolkit::column($courses, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
        $userIds = ArrayToolkit::column($joinLessonDetail, 'user_id');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $joinLessonStartData = $this->getMemberOperationService()->searchRecords(
            array('operate_type' => 'join'),
            array('operate_time' => 'ASC'),
            0,
            1
        );

        foreach ($joinLessonStartData as $key) {
            $joinLessonStartDate = date('Y-m-d', $key['operate_time']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render(
            'admin-v2/data-statistics/statistics/join-lesson.html.twig',
            array(
                'JoinLessonDetail' => $joinLessonDetail,
                'count' => $count,
                'paginator' => $paginator,
                'tab' => $tab,
                'data' => $data,
                'courses' => $courses,
                'users' => $users,
                'joinLessonStartDate' => $joinLessonStartDate,
                'dataInfo' => $dataInfo,
                'courseSets' => $courseSets,
            )
        );
    }

    public function exitLessonAction(Request $request, $tab)
    {
        $data = array();
        $exitLessonStartDate = '';

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders(
                array(
                    'paidStartTime' => $timeRange['startTime'],
                    'paidEndTime' => $timeRange['endTime'],
                    'statusPaid' => 'paid',
                    'statusCreated' => 'created',
                )
            ),
            20
        );

        $exitLessonDetail = $this->getOrderService()->searchOrders(
            array(
                'paidStartTime' => $timeRange['startTime'],
                'paidEndTime' => $timeRange['endTime'],
                'statusPaid' => 'paid',
                'statusCreated' => 'created',
            ),
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $exitLessonData = '';

        if ('trend' == $tab) {
            $exitLessonData = $this->getOrderService()->analysisCourseOrderDataByTimeAndStatus(
                $timeRange['startTime'],
                $timeRange['endTime'],
                'paid'
            );

            $data = $this->fillAnalysisData($condition, $exitLessonData);
        }

        $courseIds = ArrayToolkit::column($exitLessonDetail, 'targetId');

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $userIds = ArrayToolkit::column($exitLessonDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $cancelledOrders = $this->getOrderService()->findRefundsByIds(
            ArrayToolkit::column($exitLessonDetail, 'refundId')
        );

        $cancelledOrders = ArrayToolkit::index($cancelledOrders, 'id');

        $exitLessonStartData = $this->getOrderService()->searchOrders(
            array('statusPaid' => 'paid', 'statusCreated' => 'created'),
            'early',
            0,
            1
        );

        foreach ($exitLessonStartData as $key) {
            $exitLessonStartDate = date('Y-m-d', $key['createdTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render(
            'admin-v2/data-statistics/statistics/exit-lesson.html.twig',
            array(
                'exitLessonDetail' => $exitLessonDetail,
                'paginator' => $paginator,
                'tab' => $tab,
                'data' => $data,
                'courses' => $courses,
                'users' => $users,
                'exitLessonStartDate' => $exitLessonStartDate,
                'cancelledOrders' => $cancelledOrders,
                'dataInfo' => $dataInfo,
            )
        );
    }

    public function paidCourseAction(Request $request, $tab)
    {
        $data = array();
        $paidCourseStartDate = '';
        $count = 0;

        $condition = $request->query->all();

        $timeRange = $this->getTimeRange($condition);

        $searchCondition = array(
            'pay_time_GT' => $timeRange['startTime'],
            'pay_time_LT' => $timeRange['endTime'],
            'statuses' => array('success', 'finished'),
            'pay_amount_GT' => '0',
            'order_item_target_type' => 'course',
        );

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders(
                $searchCondition
            ),
            20
        );

        $paidCourseDetails = $this->getOrderService()->searchOrders(
            $searchCondition,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $orderIds = ArrayToolkit::column($paidCourseDetails, 'id');
        $orderSns = ArrayToolkit::column($paidCourseDetails, 'order_sn');

        $orderItems = $this->getOrderService()->findOrderItemsByOrderIds($orderIds);
        $orderItems = ArrayToolkit::index($orderItems, 'order_id');

        $orderPaymentTrades = $this->getPayService()->findTradesByOrderSns($orderSns);
        $paymentTrades = ArrayToolkit::index($orderPaymentTrades, 'order_sn');

        foreach ($paidCourseDetails as &$paidCourseDetail) {
            $paidCourseDetail['item'] = empty($orderItems[$paidCourseDetail['id']]) ? array() : $orderItems[$paidCourseDetail['id']];
            $paidCourseDetail['course_id'] = empty($paidCourseDetail['item']) ? 0 : $paidCourseDetail['item']['target_id'];
            $paidCourseDetail['trade'] = empty($paymentTrades[$paidCourseDetail['sn']]) ? array() : $paymentTrades[$paidCourseDetail['sn']];
            $paidCourseDetail = MathToolkit::multiply($paidCourseDetail, array('price_amount', 'pay_amount'), 0.01);
        }

        if ('trend' == $tab) {
            $paidCourseData = $this->getOrderService()->countGroupByDate(
                $searchCondition,
                'ASC'
            );
            $data = $this->fillAnalysisData($condition, $paidCourseData);
            $count = $this->sumTrendDataCount($paidCourseData);
        }

        $courseIds = ArrayToolkit::column($paidCourseDetails, 'course_id'); //订单中的课程

        $courses = $this->getCourseService()->searchCourses(//订单中的课程再剔除班级中的课程
            array('courseIds' => $courseIds, 'parentId' => '0'),
            'latest',
            0,
            count($paidCourseDetails)
        );
        $userIds = ArrayToolkit::column($paidCourseDetails, 'user_id');
        $courses = ArrayToolkit::index($courses, 'id');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $paidCourseStartData = $this->getOrderService()->searchOrders(
            array('statuses' => array('success', 'finished'), 'pay_amount_GT' => '0'),
            array('created_time' => 'ASC'),
            0,
            1
        );

        foreach ($paidCourseStartData as $key) {
            $paidCourseStartDate = date('Y-m-d', $key['created_time']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);
        $courseSets = $this->getCourseSetService()->findCourseSetsByCourseIds(ArrayToolkit::column($courses, 'id'));
        $courseSets = ArrayToolkit::index($courseSets, 'id');

        return $this->render(
            'admin-v2/data-statistics/statistics/paid-course.html.twig',
            array(
                'paidCourseDetail' => $paidCourseDetails,
                'courseSets' => $courseSets,
                'paginator' => $paginator,
                'tab' => $tab,
                'data' => $data,
                'courses' => $courses,
                'users' => $users,
                'paidCourseStartDate' => $paidCourseStartDate,
                'dataInfo' => $dataInfo,
                'count' => $count,
            )
        );
    }

    public function paidClassroomAction(Request $request, $tab)
    {
        $data = array();

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);
        $paidClassroomStartDate = '';

        $searchConditions = array(
            'pay_time_GT' => $timeRange['startTime'],
            'pai_time_LT' => $timeRange['endTime'],
            'statuses' => array('success', 'finished'),
            'pay_amount_GT' => '0',
            'order_item_target_type' => 'classroom',
        );
        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders(
                $searchConditions
            ),
            20
        );
        $paidClassroomDetails = $this->getOrderService()->searchOrders(
            $searchConditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $orderIds = ArrayToolkit::column($paidClassroomDetails, 'id');
        $orderSns = ArrayToolkit::column($paidClassroomDetails, 'order_sn');

        $orderItems = $this->getOrderService()->findOrderItemsByOrderIds($orderIds);
        $orderItems = ArrayToolkit::index($orderItems, 'order_id');

        $orderPaymentTrades = $this->getPayService()->findTradesByOrderSns($orderSns);
        $paymentTrades = ArrayToolkit::index($orderPaymentTrades, 'order_sn');

        foreach ($paidClassroomDetails as &$paidClassroomDetail) {
            $paidClassroomDetail['item'] = empty($orderItems[$paidClassroomDetail['id']]) ? array() : $orderItems[$paidClassroomDetail['id']];
            $paidClassroomDetail['classroom_id'] = empty($paidClassroomDetail['item']) ? 0 : $paidClassroomDetail['item']['target_id'];
            $paidClassroomDetail['trade'] = empty($paymentTrades[$paidClassroomDetail['sn']]) ? array() : $paymentTrades[$paidClassroomDetail['sn']];
            $paidClassroomDetail = MathToolkit::multiply($paidClassroomDetail, array('price_amount', 'pay_amount'), 0.01);
        }

        $count = 0;
        if ('trend' == $tab) {
            $paidClassroomData = $this->getOrderService()->countGroupByDate(
                $searchConditions,
                'ASC'
            );
            $data = $this->fillAnalysisData($condition, $paidClassroomData);
            $count = $this->sumTrendDataCount($paidClassroomData);
        }

        $classroomIds = ArrayToolkit::column($paidClassroomDetails, 'classroom_id');

        $classroom = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        $userIds = ArrayToolkit::column($paidClassroomDetails, 'user_id');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $paidClassroomStartData = $this->getOrderService()->searchOrders(
            array('statuses' => array('success', 'finished'), 'pay_amount_GT' => '0'),
            array('created_time' => 'ASC'),
            0,
            1
        );

        foreach ($paidClassroomStartData as $key) {
            $paidClassroomStartDate = date('Y-m-d', $key['created_time']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render(
            'admin-v2/data-statistics/statistics/paid-classroom.html.twig',
            array(
                'paidClassroomDetail' => $paidClassroomDetails,
                'paginator' => $paginator,
                'tab' => $tab,
                'data' => $data,
                'classroom' => $classroom,
                'users' => $users,
                'paidClassroomStartDate' => $paidClassroomStartDate,
                'dataInfo' => $dataInfo,
                'count' => $count,
            )
        );
    }

    public function completedTaskAction(Request $request, $tab)
    {
        $data = array();
        $completedTaskStartDate = '';
        $count = 0;

        $condition = $request->query->all();
        $timeRange = $this->getTimeRange($condition);

        $detailConditions = array(
            'finishedTime_GE' => $timeRange['startTime'],
            'finishedTime_LT' => $timeRange['endTime'],
            'status' => 'finish',
        );

        $paginator = new Paginator(
            $request,
            $this->getTaskResultService()->countTaskResults($detailConditions),
            20
        );

        $completedTaskDetail = $this->getTaskResultService()->searchTaskResults(
            $detailConditions,
            array('finishedTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if ('trend' == $tab) {
            $completedTaskData = $this->getTaskResultService()->analysisCompletedTaskDataByTime(
                $timeRange['startTime'],
                $timeRange['endTime']
            );
            $data = $this->fillAnalysisData($condition, $completedTaskData);
            $count = $this->sumTrendDataCount($completedTaskData);
        }

        $courseIds = ArrayToolkit::column($completedTaskDetail, 'courseId');

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $taskIds = ArrayToolkit::column($completedTaskDetail, 'courseTaskId');

        $tasks = ArrayToolkit::index($this->getTaskService()->findTasksByIds($taskIds), 'id');

        $courseSetIds = ArrayToolkit::column($courses, 'courseSetId');

        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);

        $userIds = ArrayToolkit::column($completedTaskDetail, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $completedTaskStartData = $this->getTaskResultService()->searchTaskResults(
            array('status' => 'finish'),
            array('finishedTime' => 'ASC'),
            0,
            1
        );

        if ($completedTaskStartData) {
            $completedTaskStartDate = date('Y-m-d', $completedTaskStartData[0]['finishedTime']);
        }

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render(
            'admin-v2/data-statistics/statistics/completed-task.html.twig',
            array(
                'completedTaskDetail' => $completedTaskDetail,
                'paginator' => $paginator,
                'tab' => $tab,
                'data' => $data,
                'courseSets' => $courseSets,
                'courses' => $courses,
                'tasks' => $tasks,
                'users' => $users,
                'completedTaskStartDate' => $completedTaskStartDate,
                'dataInfo' => $dataInfo,
                'count' => $count,
            )
        );
    }

    public function videoViewedAction(Request $request, $tab)
    {
        $data = array();
        $count = 0;
        $condition = $request->query->all();

        $timeRange = $this->getTimeRange($condition);

        $searchCondition = array(
            'fileType' => 'video',
            'startTime' => $timeRange['startTime'],
            'endTime' => $timeRange['endTime'],
        );

        $paginator = new Paginator(
            $request,
            $this->getTaskViewLog()->countViewLogs($searchCondition),
            20
        );

        $videoViewedDetail = $this->getTaskViewLog()->searchViewLogs(
            $searchCondition,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if ('trend' == $tab) {
            $videoViewedTrendData = $this->getTaskViewLog()->searchViewLogsGroupByTime(
                array('fileType' => 'video'),
                $timeRange['startTime'],
                $timeRange['endTime']
            );

            $data = $this->fillAnalysisData($condition, $videoViewedTrendData);
            $count = $this->sumTrendDataCount($videoViewedTrendData);
        }

        $taskIds = ArrayToolkit::column($videoViewedDetail, 'taskId');
        $tasks = $this->getTaskService()->findTasksByIds($taskIds);
        $tasks = ArrayToolkit::index($tasks, 'id');

        $userIds = ArrayToolkit::column($videoViewedDetail, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = ArrayToolkit::index($users, 'id');

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render(
            'admin-v2/data-statistics/statistics/video-view.html.twig',
            array(
                'videoViewedDetail' => $videoViewedDetail,
                'paginator' => $paginator,
                'tab' => $tab,
                'data' => $data,
                'tasks' => $tasks,
                'users' => $users,
                'dataInfo' => $dataInfo,
                'minCreatedTime' => date('Y-m-d', time()),
                'showHelpMessage' => 1,
                'count' => $count,
            )
        );
    }

    public function cloudVideoViewedAction(Request $request, $tab)
    {
        $data = array();
        $condition = $request->query->all();
        $count = 0;
        $timeRange = $this->getTimeRange($condition);

        $searchCondition = array(
            'fileType' => 'video',
            'fileStorage' => 'cloud',
            'startTime' => $timeRange['startTime'],
            'endTime' => $timeRange['endTime'],
        );

        $paginator = new Paginator(
            $request,
            $this->getTaskViewLog()->countViewLogs($searchCondition),
            20
        );

        $videoViewedDetail = $this->getTaskViewLog()->searchViewLogs(
            $searchCondition,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if ('trend' == $tab) {
            $videoViewedTrendData = $this->getTaskViewLog()->searchViewLogsGroupByTime(
                array('fileType' => 'video', 'fileStorage' => 'cloud'),
                $timeRange['startTime'],
                $timeRange['endTime']
            );

            $data = $this->fillAnalysisData($condition, $videoViewedTrendData);
            $count = $this->sumTrendDataCount($videoViewedTrendData);
        }

        $taskIds = ArrayToolkit::column($videoViewedDetail, 'taskId');
        $tasks = $this->getTaskService()->findTasksByIds($taskIds);
        $tasks = ArrayToolkit::index($tasks, 'id');

        $userIds = ArrayToolkit::column($videoViewedDetail, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = ArrayToolkit::index($users, 'id');

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render(
            'admin-v2/data-statistics/statistics/cloud-video-view.html.twig',
            array(
                'videoViewedDetail' => $videoViewedDetail,
                'paginator' => $paginator,
                'tab' => $tab,
                'data' => $data,
                'tasks' => $tasks,
                'users' => $users,
                'dataInfo' => $dataInfo,
                'minCreatedTime' => date('Y-m-d', time()),
                'showHelpMessage' => 1,
                'count' => $count,
            )
        );
    }

    public function localVideoViewedAction(Request $request, $tab)
    {
        $data = array();
        $condition = $request->query->all();
        $count = 0;
        $timeRange = $this->getTimeRange($condition);

        $searchCondition = array(
            'fileType' => 'video',
            'fileStorage' => 'local',
            'startTime' => $timeRange['startTime'],
            'endTime' => $timeRange['endTime'],
        );

        $paginator = new Paginator(
            $request,
            $this->getTaskViewLog()->countViewLogs($searchCondition),
            20
        );

        $videoViewedDetail = $this->getTaskViewLog()->searchViewLogs(
            $searchCondition,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $videoViewedTrendData = '';

        if ('trend' == $tab) {
            $videoViewedTrendData = $this->getTaskViewLog()->searchViewLogsGroupByTime(
                array('fileType' => 'video', 'fileStorage' => 'local'),
                $timeRange['startTime'],
                $timeRange['endTime']
            );

            $data = $this->fillAnalysisData($condition, $videoViewedTrendData);
            $count = $this->sumTrendDataCount($videoViewedTrendData);
        }

        $taskIds = ArrayToolkit::column($videoViewedDetail, 'taskId');
        $tasks = $this->getTaskService()->findTasksByIds($taskIds);
        $tasks = ArrayToolkit::index($tasks, 'id');

        $userIds = ArrayToolkit::column($videoViewedDetail, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = ArrayToolkit::index($users, 'id');

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render(
            'admin-v2/data-statistics/statistics/local-video-view.html.twig',
            array(
                'videoViewedDetail' => $videoViewedDetail,
                'paginator' => $paginator,
                'tab' => $tab,
                'data' => $data,
                'tasks' => $tasks,
                'users' => $users,
                'dataInfo' => $dataInfo,
                'minCreatedTime' => date('Y-m-d', time()),
                'showHelpMessage' => 1,
                'count' => $count,
            )
        );
    }

    public function netVideoViewedAction(Request $request, $tab)
    {
        $data = array();
        $condition = $request->query->all();
        $count = 0;
        $timeRange = $this->getTimeRange($condition);

        $searchCondition = array(
            'fileType' => 'video',
            'fileStorage' => 'net',
            'startTime' => $timeRange['startTime'],
            'endTime' => $timeRange['endTime'],
        );

        $paginator = new Paginator(
            $request,
            $this->getTaskViewLog()->countViewLogs($searchCondition),
            20
        );

        $videoViewedDetail = $this->getTaskViewLog()->searchViewLogs(
            $searchCondition,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $videoViewedTrendData = '';

        if ('trend' == $tab) {
            $videoViewedTrendData = $this->getTaskViewLog()->searchViewLogsGroupByTime(
                array('fileType' => 'video', 'fileStorage' => 'net'),
                $timeRange['startTime'],
                $timeRange['endTime']
            );

            $data = $this->fillAnalysisData($condition, $videoViewedTrendData);
            $count = $this->sumTrendDataCount($videoViewedTrendData);
        }

        $taskIds = ArrayToolkit::column($videoViewedDetail, 'taskId');
        $tasks = $this->getTaskService()->findTasksByIds($taskIds);
        $tasks = ArrayToolkit::index($tasks, 'id');

        $userIds = ArrayToolkit::column($videoViewedDetail, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = ArrayToolkit::index($users, 'id');

        $dataInfo = $this->getDataInfo($condition, $timeRange);

        return $this->render(
            'admin-v2/data-statistics/statistics/net-video-view.html.twig',
            array(
                'videoViewedDetail' => $videoViewedDetail,
                'paginator' => $paginator,
                'tab' => $tab,
                'data' => $data,
                'tasks' => $tasks,
                'users' => $users,
                'minCreatedTime' => date('Y-m-d', time()),
                'dataInfo' => $dataInfo,
                'showHelpMessage' => 1,
                'count' => $count,
            )
        );
    }

    public function incomeAction(Request $request, $tab, $type = null)
    {
        $data = array();
        $count = 0;

        $fields = $request->query->all();
        $timeRange = $this->getTimeRange($fields);
        $conditions = array(
            'pay_time_GT' => $timeRange['startTime'],
            'pay_time_LT' => $timeRange['endTime'],
            'statuses' => array('success', 'finished'),
            'pay_amount_GT' => 0,
        );

        if (!empty($type)) {
            $conditions['order_item_target_type'] = $type;
        }

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders($conditions),
            20
        );

        $incomeDetails = $this->getOrderService()->searchOrders(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $orderIds = ArrayToolkit::column($incomeDetails, 'id');
        $orderSns = ArrayToolkit::column($incomeDetails, 'order_sn');

        $orderItems = ArrayToolkit::index($this->getOrderService()->findOrderItemsByOrderIds($orderIds), 'order_id');
        $paymentTrades = ArrayToolkit::index($this->getPayService()->findTradesByOrderSns($orderSns), 'order_sn');

        foreach ($incomeDetails as &$incomeDetail) {
            $incomeDetail['item'] = empty($orderItems[$incomeDetail['id']]) ? array() : $orderItems[$incomeDetail['id']];
            $incomeDetail[$type.'_id'] = empty($incomeDetail['item']) ? 0 : $incomeDetail['item']['target_id'];
            $incomeDetail['trade'] = empty($paymentTrades[$incomeDetail['sn']]) ? array() : $paymentTrades[$incomeDetail['sn']];
        }

        if ('trend' == $tab) {
            $incomeData = $this->getOrderService()->sumGroupByDate(
                'pay_amount',
                $conditions,
                'ASC'
            );
            foreach ($incomeData as &$tmpData) {
                $tmpData = MathToolkit::multiply($tmpData, array('count'), 0.01);
            }
            $data = $this->fillAnalysisData($fields, $incomeData);
            $count = $this->sumTrendDataCount($incomeData);
        }

        $targetIds = ArrayToolkit::column($incomeDetails, $type.'_id');
        $targetItems = $this->getItemsByIdsAndType($targetIds, $type);

        $userIds = ArrayToolkit::column($incomeDetails, 'user_id');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $dataInfo = $this->getDataInfo($fields, $timeRange);

        return $this->render(
            'admin-v2/data-statistics/statistics/'.(empty($type) ? 'all' : $type).'-income.html.twig',
            array(
                'orders' => $incomeDetails,
                'paginator' => $paginator,
                'tab' => $tab,
                'data' => $data,
                $type.'s' => $targetItems,
                'users' => $users,
                'dataInfo' => $dataInfo,
                'count' => $count,
            )
        );
    }

    private function getItemsByIdsAndType($targetIds, $type)
    {
        switch ($type) {
            case 'course':
                $items = $this->getCourseService()->findCoursesByIds($targetIds);
                break;
            case 'classroom':
                $items = $this->getClassroomService()->findClassroomsByIds($targetIds);
                break;
            default:
                $items = array();
        }

        return $items;
    }

    public function courseSetIncomeAction(Request $request, $tab)
    {
        return $this->forward(
            'AppBundle:AdminV2/DataStatistics/Statistics:income',
            array(
                'request' => $request,
                'tab' => $tab,
                'type' => 'course',
            )
        );
    }

    public function classroomIncomeAction(Request $request, $tab)
    {
        return $this->forward(
            'AppBundle:AdminV2/DataStatistics/Statistics:income',
            array(
                'request' => $request,
                'tab' => $tab,
                'type' => 'classroom',
            )
        );
    }

    public function vipIncomeAction(Request $request, $tab)
    {
        return $this->forward(
            'AppBundle:AdminV2/DataStatistics/Statistics:income',
            array(
                'request' => $request,
                'tab' => $tab,
                'type' => 'vip',
            )
        );
    }

    protected function sumTrendDataCount(array $trendData)
    {
        return array_reduce($trendData, function ($count, $data) {
            $count += $data['count'];

            return $count;
        }, 0);
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

        for ($i = 0; $i < count($initData); ++$i) {
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
            $zeroData[] = array('date' => $value, 'count' => 0);
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
            'startTime' => date('Y-m-d', $timeRange['startTime']),
            'endTime' => date('Y-m-d', $timeRange['endTime']),
            'currentMonthStart' => date('Y-m-d', strtotime(date('Y-m', time()))),
            'currentMonthEnd' => date('Y-m-d', time()),
            'lastMonthStart' => date('Y-m-d', strtotime(date('Y-m', strtotime('-1 month')))),
            'lastMonthEnd' => date('Y-m-d', strtotime(date('Y-m', time())) - 24 * 3600),
            'lastThreeMonthsStart' => date('Y-m-d', strtotime(date('Y-m', strtotime('-2 month')))),
            'lastThreeMonthsEnd' => date('Y-m-d', time()),
            'analysisDateType' => $condition['analysisDateType'],
        );
    }

    protected function getTimeRange($fields)
    {
        $startTime = !empty($fields['startTime']) ? $fields['startTime'] : date('Y-m', time());
        $endTime = !empty($fields['endTime']) ? $fields['endTime'] : date('Y-m-d', time());

        return array(
            'startTime' => strtotime($startTime),
            'endTime' => strtotime($endTime) + 24 * 3600 - 1,
        );
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ViewLogService
     */
    protected function getTaskViewLog()
    {
        return $this->createService('Task:ViewLogService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->createService('Pay:PayService');
    }

    /**
     * @return MemberOperationService
     */
    public function getMemberOperationService()
    {
        return $this->createService('MemberOperation:MemberOperationService');
    }
}
