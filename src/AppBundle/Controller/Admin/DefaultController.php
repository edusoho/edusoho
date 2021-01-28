<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\CurlToolkit;
use AppBundle\Component\Echats\EchartsBuilder;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Common\CommonException;
use Biz\Review\Service\ReviewService;
use Codeages\Biz\Order\Service\OrderService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Topxia\Service\Common\ServiceKernel;
use VipPlugin\Biz\Vip\Service\VipService;

class DefaultController extends BaseController
{
    public function renderCurrentAdminHomepageAction($permission)
    {
        $tabMenu = $this->container->get('permission.twig.permission_extension')->getFirstChild($permission);
        $tabMenu = $this->container->get('permission.twig.permission_extension')->getFirstChild($tabMenu);

        if (!empty($tabMenu['mode']) && 'capsules' == $tabMenu['mode']) {
            $tabMenu = $this->container->get('permission.twig.permission_extension')->getFirstChild($tabMenu);
        }

        $permissionPath = $this->container->get('permission.twig.permission_extension')->getPermissionPath($this, ['needs_context' => true, 'needs_environment' => true], $tabMenu);

        return $this->redirect($permissionPath);
    }

    public function indexAction(Request $request)
    {
        $weekAndMonthDate = ['weekDate' => date('Y-m-d', time() - 6 * 24 * 60 * 60), 'monthDate' => date('Y-m-d', time() - 29 * 24 * 60 * 60)];

        return $this->render('admin/default/index.html.twig', [
            'dates' => $weekAndMonthDate,
        ]);
    }

    public function feedbackAction(Request $request)
    {
        $site = $this->getSettingService()->get('site');
        $user = $this->getUser();
        $token = CurlToolkit::request('POST', 'http://www.edusoho.com/question/get/token', []);
        $site = ['name' => $site['name'], 'url' => $site['url'], 'token' => $token, 'username' => $user->nickname];
        $site = urlencode(http_build_query($site));

        return $this->redirect('http://www.edusoho.com/question?site='.$site.'');
    }

    public function validateDomainAction(Request $request)
    {
        $inspectList = [
            $this->addInspectRole('host', $this->domainInspect($request)),
        ];
        $inspectList = array_filter($inspectList);

        return $this->render('admin/default/domain.html.twig', [
            'inspectList' => $inspectList,
        ]);
    }

    private function addInspectRole($name, $value)
    {
        if ('ok' == $value['status']) {
            return [];
        }

        return ['name' => $name, 'value' => $value];
    }

    private function domainInspect($request)
    {
        $currentHost = $request->server->get('HTTP_HOST');
        $siteSetting = $this->getSettingService()->get('site');
        $settingUrl = $this->generateUrl('admin_setting_site');
        $filter = ['http://', 'https://'];
        $siteSetting['url'] = rtrim($siteSetting['url']);
        $siteSetting['url'] = rtrim($siteSetting['url'], '/');

        if ($currentHost != str_replace($filter, '', $siteSetting['url'])) {
            return [
                'status' => 'warning',
                'errorMessage' => ServiceKernel::instance()->trans('admin.domain_error_hint'),
                'except' => $siteSetting['url'],
                'actually' => $currentHost,
                'settingUrl' => $settingUrl,
            ];
        }

        return ['status' => 'ok', 'except' => $siteSetting['url'], 'actually' => $currentHost, 'settingUrl' => $settingUrl];
    }

    public function getCloudNoticesAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            $domain = $this->generateUrl('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $api = CloudAPIFactory::create('root');
            $result = $api->get('/trial/remainDays', ['domain' => $domain]);

            return $this->render('admin/default/cloud-notice.html.twig', [
                'trialTime' => (isset($result)) ? $result : null,
            ]);
        } elseif ($this->getWebExtension()->isWithoutNetwork()) {
            $notices = [];
        } else {
            $notices = $this->getNoticesFromOpen();
        }

        return $this->render('admin/default/cloud-notice.html.twig', [
            'notices' => $notices,
        ]);
    }

    private function getNoticesFromOpen()
    {
        $url = 'http://open.edusoho.com/api/v1/context/notice';

        return CurlToolkit::request('GET', $url);
    }

    public function systemStatusAction()
    {
        $apps = $this->getAppService()->checkAppUpgrades();

        $upgradeAppCount = count($apps);

        $indexApps = ArrayToolkit::index($apps, 'code');
        $mainAppUpgrade = empty($indexApps['MAIN']) ? [] : $indexApps['MAIN'];

        if ($mainAppUpgrade) {
            $upgradeAppCount = $upgradeAppCount - 1;
        }

        return $this->render('admin/default/system-status.html.twig', [
            'mainAppUpgrade' => $mainAppUpgrade,
            'upgradeAppCount' => $upgradeAppCount,
            'disabledCloudServiceCount' => $this->getDisabledCloudServiceCount(),
        ]);
    }

    protected function getDisabledCloudServiceCount()
    {
        $disabledCloudServiceCount = 0;

        $settingKeys = [
            'course.live_course_enabled' => '',
            'cloud_sms.sms_enabled' => '',
            'cloud_search.search_enabled' => '',
            'cloud_consult.cloud_consult_setting_enabled' => 0,
            'storage.upload_mode' => 'cloud',
        ];

        foreach ($settingKeys as $settingName => $expect) {
            $value = $this->setting($settingName);
            if (empty($expect)) {
                $disabledCloudServiceCount += empty($value) ? 1 : 0;
            } else {
                $disabledCloudServiceCount += empty($value) || $value != $expect ? 2 : 0;
            }
        }

        return $disabledCloudServiceCount;
    }

    public function operationAnalysisDashbordBlockAction(Request $request)
    {
        $todayTimeStart = strtotime(date('Y-m-d', time()));
        $todayTimeEnd = strtotime(date('Y-m-d', time() + 24 * 3600));

        $onlineCount = $this->getStatisticsService()->countOnline(time() - 15 * 60);
        $loginCount = $this->getStatisticsService()->countLogin(time() - 15 * 60);

        $todayRegisterNum = $this->getUserService()->countUsers(['startTime' => $todayTimeStart, 'endTime' => $todayTimeEnd]);
        $totalRegisterNum = $this->getUserService()->countUsers([]);

        $todayCourseMemberNum = $this->getMemberOperationService()->countRecords(['operate_time_GE' => $todayTimeStart, 'operate_time_LT' => $todayTimeEnd, 'target_type' => 'course', 'operate_type' => 'join']);
        $todayClassroomMemberNum = $this->getMemberOperationService()->countRecords(['operate_time_GE' => $todayTimeStart, 'operate_time_LT' => $todayTimeEnd, 'target_type' => 'classroom', 'operate_type' => 'join', 'exclude_reason_type' => 'auditor_join']);

        $totalCourseMemberNum = $this->getMemberOperationService()->countRecords(['target_type' => 'course', 'operate_type' => 'join']);
        $totalClassroomMemberNum = $this->getMemberOperationService()->countRecords(['target_type' => 'classroom', 'operate_type' => 'join', 'exclude_reason_type' => 'auditor_join']);

        $todayVipNum = 0;
        $totalVipNum = 0;
        if ($this->isPluginInstalled('vip')) {
            $totalVipNum = $this->getVipService()->searchMembersCount([]);
            $todayVipNum = $this->getMemberOperationService()->countUserIdsByConditions(['operate_time_GE' => $todayTimeStart, 'operate_time_LT' => $todayTimeEnd, 'target_type' => 'vip', 'operate_type' => 'join']);
        }

        $toInvoiceNum = 0;
        $totalInvoiceNum = 0;
        if ($this->isPluginInstalled('Invoice')) {
            $totalInvoiceNum = $this->getInvoiceService()->countInvoices([]);
            $toInvoiceNum = $this->getInvoiceService()->countInvoices(['status' => 'unchecked']);
        }

        $todayThreadUnAnswerNum = $this->getThreadService()->countThreads(['startCreatedTime' => $todayTimeStart, 'endCreatedTime' => $todayTimeEnd, 'postNum' => 0, 'type' => 'question']);
        $totalThreadNum = $this->getThreadService()->countThreads(['postNum' => 0, 'type' => 'question']);

        return $this->render('admin/default/operation-analysis-dashbord.html.twig', [
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
        ]);
    }

    public function userStatisticAction(Request $request, $period)
    {
        $series = [];
        $days = $this->getDaysDiff($period);
        $timeRange = $this->getTimeRange($period);

        //每日注册用户
        $series['registerCount'] = $this->getRegisterCount($timeRange);

        //活跃用户
        $series['activeUserCount'] = $this->getActiveuserCount($days);

        //每日注册总数
        $series['registerTotalCount'] = $this->getRegisterTotalCount($timeRange, $days);

        $userAnalysis = EchartsBuilder::createLineDefaultData($days, 'Y/m/d', $series);

        //流失用户
        $userAnalysis['series']['lostUserCount'] = $this->getLostUserCount($userAnalysis);

        return $this->createJsonResponse($userAnalysis);
    }

    public function completedTaskStatisticAction(Request $request, $period)
    {
        $days = $this->getDaysDiff($period);
        $series = [];
        $timeRange = $this->getTimeRange($period);
        $finishedTaskData = $this->getTaskResultService()->analysisCompletedTaskDataByTime($timeRange['startTime'], $timeRange['endTime']);
        $series['finishedTaskCount'] = $finishedTaskData;

        return $this->createJsonResponse(EchartsBuilder::createBarDefaultData($days, 'Y/m/d', $series));
    }

    /**
     * 订单统计
     *
     * @param  $period
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function studyStatisticAction(Request $request, $period)
    {
        $series = [];
        $days = $this->getDaysDiff($period);
        $timeRange = $this->getTimeRange($period);

        $conditions = ['pay_time_GT' => $timeRange['startTime'], 'pay_time_LT' => $timeRange['endTime'], 'statuses' => ['paid', 'success', 'finished', 'refunded']];
        $newOrders = $this->getOrderService()->countGroupByDate($conditions, 'ASC');
        $series['newOrderCount'] = $newOrders;

        $conditions['pay_amount_GT'] = 0;
        $newPaidOrders = $this->getOrderService()->countGroupByDate($conditions, 'ASC');
        $series['newPaidOrderCount'] = $newPaidOrders;

        $userAnalysis = EchartsBuilder::createLineDefaultData($days, 'Y/m/d', $series);

        return $this->createJsonResponse($userAnalysis);
    }

    public function orderStatisticAction(Request $request, $period)
    {
        $days = $this->getDaysDiff($period);

        $startTime = strtotime(date('Y-m-d', time() - $days * 24 * 60 * 60));
        $conditions = [
            'pay_time_GT' => $startTime,
            'target_type' => 'course',
            'pay_amount_GT' => 0,
            'statuses' => ['paid', 'success', 'finished', 'refunded'],
        ];

        $courseOrdersCount = $this->getOrderService()->countOrderItems($conditions);

        $conditions['target_type'] = 'classroom';
        $classroomOrdersCount = $this->getOrderService()->countOrderItems($conditions);

        if ($this->isPluginInstalled('vip')) {
            $conditions['target_type'] = 'vip';
            $vipOrdersCount = $this->getOrderService()->countOrderItems($conditions);
        }

        $orderDatas = [
            'course' => ['targetType' => 'course', 'value' => $courseOrdersCount],
            'vip' => ['targetType' => 'vip', 'value' => isset($vipOrdersCount) ? $vipOrdersCount : 0],
            'classroom' => ['targetType' => 'classroom', 'value' => $classroomOrdersCount],
        ];

        $defaults = [
            'course' => ['targetType' => 'course', 'value' => 0],
            'vip' => ['targetType' => 'vip', 'value' => 0],
            'classroom' => ['targetType' => 'classroom', 'value' => 0],
        ];
        $orderDatas = ArrayToolkit::index($orderDatas, 'targetType');
        $orderDatas = array_merge($defaults, $orderDatas);

        $names = ['course' => ServiceKernel::instance()->trans('admin.index.course_order'), 'vip' => ServiceKernel::instance()->trans('admin.index.vip_order'), 'classroom' => ServiceKernel::instance()->trans('admin.index.classroom_order')];
        array_walk($orderDatas, function (&$orderData) use ($names) {
            $orderData['name'] = $names[$orderData['targetType']];
            unset($orderData['targetType']);
        });
        if (!$this->isPluginInstalled('vip')) {
            unset($orderDatas['vip']);
        }

        return $this->createJsonResponse(array_values($orderDatas));
    }

    public function courseExploreAction(Request $request, $period)
    {
        $days = $this->getDaysDiff($period);
        $startTime = strtotime(date('Y-m-d', time() - $days * 24 * 60 * 60));

        $memberCounts = $this->getCourseMemberService()->searchMemberCountGroupByFields(['startTimeGreaterThan' => $startTime, 'classroomId' => 0, 'role' => 'student'], 'courseSetId', 0, 10);
        $courseSetIds = ArrayToolkit::column($memberCounts, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
        $courseSets = ArrayToolkit::index($courseSets, 'id');

        return $this->render('admin/default/parts/course-explore-table.html.twig', [
            'memberCounts' => $memberCounts,
            'courseSets' => $courseSets,
        ]);
    }

    public function courseReviewAction(Request $request)
    {
        $reviews = $this->getReviewService()->searchReviews(
            ['parentId' => 0, 'targetType' => 'course'],
            ['createdTime' => 'DESC'],
            0,
            10
        );

        return $this->render('admin/default/parts/course-review-table.html.twig', [
            'reviews' => $reviews,
        ]);
    }

    public function unsolvedQuestionsBlockAction(Request $request)
    {
        $questions = $this->getThreadService()->searchThreads(['type' => 'question', 'postNum' => 0], 'createdNotStick', 0, 10);

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($questions, 'courseId'));

        return $this->render('admin/default/unsolved-questions-block.html.twig', [
            'questions' => $questions,
            'courses' => $courses,
        ]);
    }

    public function questionRemindTeachersAction(Request $request, $courseId, $questionId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $question = $this->getThreadService()->getThread($courseId, $questionId);

        $message = [
            'courseTitle' => $courseSet['title'],
            'courseId' => $course['id'],
            'threadId' => $question['id'],
            'questionTitle' => strip_tags($question['title']),
        ];

        foreach ($course['teacherIds'] as $receiverId) {
            $result = $this->getNotificationService()->notify($receiverId, 'questionRemind', $message);
        }

        return $this->createJsonResponse(['success' => true, 'message' => 'ok']);
    }

    public function cloudSearchRankingAction(Request $request)
    {
        $api = CloudAPIFactory::create('root');
        $result = $api->get('/search/words/ranking', []);
        $searchRanking = isset($result['items']) ? $result['items'] : [];

        return $this->render('admin/default/cloud-search-ranking.html.twig', ['searchRankings' => $searchRanking]);
    }

    public function weekday($time)
    {
        if (is_numeric($time)) {
            $weekday = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];

            return $weekday[date('w', $time)];
        }

        return false;
    }

    public function upgradeV2SettingAction(Request $request)
    {
        $setting = $this->getSettingService()->get('backstage', ['is_v2' => 0]);

        if (!empty($setting) && $setting['is_v2']) {
            $this->createNewException(CommonException::UPGRADE_V2_ERROR());
        }
        $user = $this->getCurrentUser();
        if (0 == count(array_intersect($user['roles'], ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']))) {
            return $this->createJsonResponse(['status' => 'error', 'message' => $this->trans('admin_v2.upgrade_v2_setting_permission.error')]);
        }
        $setting['is_v2'] = 1;
        $this->getSettingService()->set('backstage', $setting);
        $this->pushEventTracking('switchToAdminV2');

        return $this->createJsonResponse(['status' => 'success', 'url' => $this->generateUrl('admin_v2')]);
    }

    private function getRegisterCount($timeRange)
    {
        $analysisRegister = $this->getUserService()->analysisRegisterDataByTime($timeRange['startTime'], $timeRange['endTime']);

        return $analysisRegister;
    }

    private function getActiveuserCount($days)
    {
        $active_days = '30';
        $activeAnalysis = $this->getUserActiveService()->analysisActiveUser(strtotime(date('Y-m-d', time() - ($days + $active_days) * 24 * 60 * 60)), strtotime(date('Y-m-d', time() + 24 * 60 * 60)));
        $activeAnalysis = $this->fillActiveUserCount($days, $activeAnalysis);

        return $activeAnalysis;
    }

    private function getRegisterTotalCount($timeRange, $days)
    {
        $registerCount = $this->getUserService()->countUsersByLessThanCreatedTime($timeRange['startTime']);
        $dayRegisterTotal = $this->getUserService()->analysisRegisterDataByTime($timeRange['startTime'], $timeRange['endTime']);
        $dayRegisterTotal = $this->fillAnalysisUserSum($registerCount, $dayRegisterTotal, $days);

        return $dayRegisterTotal;
    }

    private function getLostUserCount($userAnalysis)
    {
        $lostUserCount = [];

        $dayRegisterTotal = $userAnalysis['series']['registerTotalCount'];
        $activeUserCount = $userAnalysis['series']['activeUserCount'];
        array_walk($dayRegisterTotal, function ($value, $index) use (&$lostUserCount, $activeUserCount) {
            $lostUserCount[] = $value - $activeUserCount[$index];
        });

        return $lostUserCount;
    }

    private function getDaysDiff($period)
    {
        $days = 'week' == $period ? 6 : 29;

        return $days;
    }

    protected function getTimeRange($period)
    {
        $days = $this->getDaysDiff($period);

        return ['startTime' => strtotime(date('Y-m-d', time() - $days * 24 * 60 * 60)), 'endTime' => strtotime(date('Y-m-d', time() + 24 * 3600))];
    }

    protected function makeDateRange($startTime, $endTime)
    {
        $dates = [];

        $currentTime = $startTime;

        while (true) {
            if ($currentTime >= $endTime) {
                break;
            }

            $currentDate = date('Y-m-d', $currentTime);
            $dates[] = $currentDate;

            $currentTime = $currentTime + 3600 * 24;
        }

        return $dates;
    }

    protected function generateDateRange($days, $format = 'Y/m/d')
    {
        $dates = [];
        for ($i = $days; $i >= 0; --$i) {
            $dates[] = date($format, time() - $i * 24 * 60 * 60);
        }

        return $dates;
    }

    protected function fillActiveUserCount($days, $activeAnalysis)
    {
        $xAxisDate = $this->generateDateRange($days, 'Y-m-d');
        $result = [];
        array_walk($xAxisDate, function ($date) use ($activeAnalysis, &$result) {
            foreach ($activeAnalysis as $index => $value) {
                //在30天内登录过系统的用户即为活跃用户
                $diff = (strtotime($date) - strtotime($value['date'])) / 86400;
                if ($diff >= 0 && $diff <= 30) {
                    $result[$date][] = $value['userId'];
                }
            }
            if (empty($result[$date])) {
                $result[$date] = [];
            }
        });

        array_walk($result, function (&$data, $key) {
            $data = ['count' => count(array_unique($data)), 'date' => $key];
        });

        return $result; //array_values($result);
    }

    //获取每天的注册总数
    protected function fillAnalysisUserSum($registerCount, $dayRegisterTotal, $days)
    {
        $dayRegisterTotal = ArrayToolkit::index($dayRegisterTotal, 'date');

        $xAxisDate = $this->generateDateRange($days, 'Y-m-d');
        foreach ($xAxisDate as $date) {
            $zeroAnalysis[$date] = ['count' => 0, 'date' => $date];
        }

        $dayRegisterTotal = array_merge($zeroAnalysis, $dayRegisterTotal);

        $previousRegisterTotalCount = 0;
        array_walk($dayRegisterTotal, function (&$data) use ($registerCount, &$previousRegisterTotalCount) {
            //累加前一天的计算总数
            $data['count'] += empty($previousRegisterTotalCount) ? $registerCount : $previousRegisterTotalCount;
            $previousRegisterTotalCount = $data['count'];
        });

        return $dayRegisterTotal;
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getStatisticsService()
    {
        return $this->createService('System:StatisticsService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    protected function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    protected function getUpgradeNoticeService()
    {
        return $this->createService('User:UpgradeNoticeService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->createService('Review:ReviewService');
    }

    protected function getUserActiveService()
    {
        return $this->createService('User:UserActiveService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }

    /**
     * @return \Codeages\Biz\Invoice\Service\Impl\InvoiceServiceImpl
     */
    protected function getInvoiceService()
    {
        return $this->createService('Invoice:InvoiceService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getMemberOperationService()
    {
        return $this->createService('MemberOperation:MemberOperationService');
    }

    protected function isPluginInstalled($name)
    {
        return $this->get('web.twig.extension')->isPluginInstalled($name);
    }
}
