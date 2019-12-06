<?php

namespace AppBundle\Controller\AdminV2\DataStatistics;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Echats\EchartsBuilder;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ReviewService;
use Biz\Course\Service\ThreadService;
use Biz\MemberOperation\Service\MemberOperationService;
use Biz\System\Service\StatisticsService;
use Biz\Task\Service\TaskResultService;
use Codeages\Biz\Invoice\Service\InvoiceService;
use Codeages\Biz\Order\Service\OrderService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
use VipPlugin\Biz\Vip\Service\VipService;

class OverviewController extends BaseController
{
    public function indexAction(Request $request)
    {
        $weekAndMonthDate = array('weekDate' => date('Y-m-d', time() - 6 * 24 * 60 * 60), 'monthDate' => date('Y-m-d', time() - 29 * 24 * 60 * 60));

        return $this->render('admin-v2/data-statistics/overview/index.html.twig', array(
            'dates' => $weekAndMonthDate,
        ));
    }

    public function dailyAction(Request $request)
    {
        $todayTimeStart = strtotime(date('Y-m-d', time()));
        $todayTimeEnd = strtotime(date('Y-m-d', time() + 24 * 3600));

        $onlineCount = $this->getStatisticsService()->countOnline(time() - 15 * 60);
        $loginCount = $this->getStatisticsService()->countLogin(time() - 15 * 60);

        $todayRegisterNum = $this->getUserService()->countUsers(array('startTime' => $todayTimeStart, 'endTime' => $todayTimeEnd));
        $totalRegisterNum = $this->getUserService()->countUsers(array());

        $todayCourseMemberNum = $this->getMemberOperationService()->countRecords(array('operate_time_GE' => $todayTimeStart, 'operate_time_LT' => $todayTimeEnd, 'target_type' => 'course', 'operate_type' => 'join'));
        $todayClassroomMemberNum = $this->getMemberOperationService()->countRecords(array('operate_time_GE' => $todayTimeStart, 'operate_time_LT' => $todayTimeEnd, 'target_type' => 'classroom', 'operate_type' => 'join', 'exclude_reason_type' => 'auditor_join'));

        $totalCourseMemberNum = $this->getMemberOperationService()->countRecords(array('target_type' => 'course', 'operate_type' => 'join'));
        $totalClassroomMemberNum = $this->getMemberOperationService()->countRecords(array('target_type' => 'classroom', 'operate_type' => 'join', 'exclude_reason_type' => 'auditor_join'));

        $todayVipNum = 0;
        $totalVipNum = 0;
        if ($this->isPluginInstalled('vip')) {
            $totalVipNum = $this->getVipService()->searchMembersCount(array());
            $todayVipNum = $this->getMemberOperationService()->countUserIdsByConditions(array('operate_time_GE' => $todayTimeStart, 'operate_time_LT' => $todayTimeEnd, 'target_type' => 'vip', 'operate_type' => 'join'));
        }

        $toInvoiceNum = 0;
        $totalInvoiceNum = 0;
        if ($this->isPluginInstalled('Invoice')) {
            $totalInvoiceNum = $this->getInvoiceService()->countInvoices(array());
            $toInvoiceNum = $this->getInvoiceService()->countInvoices(array('status' => 'unchecked'));
        }

        $todayThreadUnAnswerNum = $this->getThreadService()->countThreads(array('startCreatedTime' => $todayTimeStart, 'endCreatedTime' => $todayTimeEnd, 'postNum' => 0, 'type' => 'question'));
        $totalThreadNum = $this->getThreadService()->countThreads(array('postNum' => 0, 'type' => 'question'));

        return $this->render('admin-v2/data-statistics/overview/daily.html.twig', array(
            'onlineCount' => $onlineCount,
            'loginCount' => $loginCount,

            'todayRegisterNum' => $todayRegisterNum,
            'totalRegisterNum' => $totalRegisterNum,

            'todayCourseMemberNum' => $todayCourseMemberNum,
            'totalCourseMemberNum' => $totalCourseMemberNum,

            'todayClassroomMemberNum' => $todayClassroomMemberNum,
            'totalClassroomMemberNum' => $totalClassroomMemberNum,

            'todayVipNum' => $todayVipNum,
            'totalVipNum' => $totalVipNum,

            'todayThreadUnAnswerNum' => $todayThreadUnAnswerNum,
            'totalThreadNum' => $totalThreadNum,

            'totalInvoiceNum' => $totalInvoiceNum,
            'toInvoiceNum' => $toInvoiceNum,
        ));
    }

    public function studyAction(Request $request, $period)
    {
        $series = array();
        $days = $this->getDaysDiff($period);
        $timeRange = $this->getTimeRange($period);

        $conditions = array('pay_time_GT' => $timeRange['startTime'], 'pay_time_LT' => $timeRange['endTime'], 'statuses' => array('paid', 'success', 'finished', 'refunded'));
        $newOrders = $this->getOrderService()->countGroupByDate($conditions, 'ASC');
        $series['newOrderCount'] = $newOrders;

        $conditions['pay_amount_GT'] = 0;
        $newPaidOrders = $this->getOrderService()->countGroupByDate($conditions, 'ASC');
        $series['newPaidOrderCount'] = $newPaidOrders;

        $userAnalysis = EchartsBuilder::createLineDefaultData($days, 'Y/m/d', $series);

        return $this->createJsonResponse($userAnalysis);
    }

    public function orderAction(Request $request, $period)
    {
        $days = $this->getDaysDiff($period);

        $startTime = strtotime(date('Y-m-d', time() - $days * 24 * 60 * 60));
        $conditions = array(
            'pay_time_GT' => $startTime,
            'target_type' => 'course',
            'pay_amount_GT' => 0,
            'statuses' => array('paid', 'success', 'finished', 'refunded'),
        );

        $courseOrdersCount = $this->getOrderService()->countOrderItems($conditions);

        $conditions['target_type'] = 'classroom';
        $classroomOrdersCount = $this->getOrderService()->countOrderItems($conditions);

        if ($this->isPluginInstalled('vip')) {
            $conditions['target_type'] = 'vip';
            $vipOrdersCount = $this->getOrderService()->countOrderItems($conditions);
        }

        $orderDatas = array(
            'course' => array('targetType' => 'course', 'value' => $courseOrdersCount),
            'vip' => array('targetType' => 'vip', 'value' => isset($vipOrdersCount) ? $vipOrdersCount : 0),
            'classroom' => array('targetType' => 'classroom', 'value' => $classroomOrdersCount),
        );

        $defaults = array(
            'course' => array('targetType' => 'course', 'value' => 0),
            'vip' => array('targetType' => 'vip', 'value' => 0),
            'classroom' => array('targetType' => 'classroom', 'value' => 0),
        );
        $orderDatas = ArrayToolkit::index($orderDatas, 'targetType');
        $orderDatas = array_merge($defaults, $orderDatas);

        $names = array('course' => ServiceKernel::instance()->trans('admin.index.course_order'), 'vip' => ServiceKernel::instance()->trans('admin.index.vip_order'), 'classroom' => ServiceKernel::instance()->trans('admin.index.classroom_order'));
        array_walk($orderDatas, function (&$orderData) use ($names) {
            $orderData['name'] = $names[$orderData['targetType']];
            unset($orderData['targetType']);
        });
        if (!$this->isPluginInstalled('vip')) {
            unset($orderDatas['vip']);
        }

        return $this->createJsonResponse(array_values($orderDatas));
    }

    public function taskLearnAction(Request $request, $period)
    {
        $days = $this->getDaysDiff($period);
        $series = array();
        $timeRange = $this->getTimeRange($period);
        $finishedTaskData = $this->getTaskResultService()->analysisCompletedTaskDataByTime($timeRange['startTime'], $timeRange['endTime']);
        $series['finishedTaskCount'] = $finishedTaskData;

        return $this->createJsonResponse(EchartsBuilder::createBarDefaultData($days, 'Y/m/d', $series));
    }

    public function courseExploreAction(Request $request, $period)
    {
        $days = $this->getDaysDiff($period);
        $startTime = strtotime(date('Y-m-d', time() - $days * 24 * 60 * 60));

        $memberCounts = $this->getCourseMemberService()->searchMemberCountGroupByFields(array('startTimeGreaterThan' => $startTime, 'classroomId' => 0, 'role' => 'student'), 'courseSetId', 0, 10);
        $courseSetIds = ArrayToolkit::column($memberCounts, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
        $courseSets = ArrayToolkit::index($courseSets, 'id');

        return $this->render('admin-v2/data-statistics/overview/course-explore-table.html.twig', array(
            'memberCounts' => $memberCounts,
            'courseSets' => $courseSets,
        ));
    }

    public function courseReviewAction(Request $request)
    {
        $reviews = $this->getReviewService()->searchReviews(
            array('parentId' => 0),
            'latest',
            0,
            10
        );

        return $this->render('admin-v2/data-statistics/overview/course-review-table.html.twig', array(
            'reviews' => $reviews,
        ));
    }

    public function unsolvedQuestionsBlockAction(Request $request)
    {
        $questions = $this->getThreadService()->searchThreads(array('type' => 'question', 'postNum' => 0), 'createdNotStick', 0, 10);

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($questions, 'courseId'));

        return $this->render('admin-v2/data-statistics/overview/unsolved-questions-block.html.twig', array(
            'questions' => $questions,
            'courses' => $courses,
        ));
    }

    public function cloudSearchRankingAction(Request $request)
    {
        $api = CloudAPIFactory::create('root');
        $result = $api->get('/search/words/ranking', array());
        $searchRanking = isset($result['items']) ? $result['items'] : array();

        return $this->render('admin-v2/data-statistics/overview/cloud-search-ranking.html.twig', array('searchRankings' => $searchRanking));
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->createService('Course:ReviewService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return StatisticsService
     */
    protected function getStatisticsService()
    {
        return $this->createService('System:StatisticsService');
    }

    /**
     * @return MemberOperationService
     */
    protected function getMemberOperationService()
    {
        return $this->createService('MemberOperation:MemberOperationService');
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }

    /**
     * @return InvoiceService
     */
    protected function getInvoiceService()
    {
        return $this->createService('Invoice:InvoiceService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    private function getDaysDiff($period)
    {
        $days = 'week' == $period ? 6 : 29;

        return $days;
    }

    private function getTimeRange($period)
    {
        $days = $this->getDaysDiff($period);

        return array('startTime' => strtotime(date('Y-m-d', time() - $days * 24 * 60 * 60)), 'endTime' => strtotime(date('Y-m-d', time() + 24 * 3600)));
    }
}
