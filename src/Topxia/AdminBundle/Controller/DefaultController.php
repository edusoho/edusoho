<?php
namespace Topxia\AdminBundle\Controller;


use Topxia\Common\CurlToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Course\Impl\CourseServiceImpl;
use Topxia\Service\Order\Impl\OrderServiceImpl;
use Topxia\Service\System\Impl\StatisticsServiceImpl;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Topxia\Service\User\Impl\UserActiveServiceImpl;

class DefaultController extends BaseController
{

    public function indexAction(Request $request)
    {

        $weekAndMonthDate = array('weekDate' => date('Y-m-d', time() - 6 * 24 * 60 * 60), 'monthDate' => date('Y-m-d', time() - 29 * 24 * 60 * 60));
        return $this->render('TopxiaAdminBundle:Default:index.html.twig', array(
            'dates' => $weekAndMonthDate
        ));
    }

    /**
     * @return UserActiveServiceImpl
     */
    private function getUserActiveService()
    {
        return $this->createService('User.UserActiveService');
    }

    public function noticeAction(Request $request)
    {
        //高级去版权用户不显示提醒
        $copyright = $this->setting('copyright', array());
        if (!empty($copyright) && $copyright['owned'] == 1 && $copyright['thirdCopyright'] == 1) {
            return $this->createJsonResponse(array('result' => false));
        }

        $user       = $this->getCurrentUser();
        $userNotice = $this->getUpgradeNoticeService()->getNoticeByUserIdAndVersionAndCode($user['id'], '7.0.0', 'MAIN');

        if ($userNotice) {
            return $this->createJsonResponse(array('result' => false));
        }

        $noticeFields = array(
            'userId'  => $user['id'],
            'version' => '7.0.0',
            'code'    => 'MAIN'
        );
        $this->getUpgradeNoticeService()->addNotice($noticeFields);

        $engine  = $this->container->get('templating');
        $content = $engine->render('TopxiaAdminBundle:Default:notice-modal.html.twig');

        return $this->createJsonResponse(array('result' => true, 'html' => $content));
    }

    public function feedbackAction(Request $request)
    {
        $site  = $this->getSettingService()->get('site');
        $user  = $this->getCurrentUser();
        $token = CurlToolkit::request('POST', "http://www.edusoho.com/question/get/token", array());
        $site  = array('name' => $site['name'], 'url' => $site['url'], 'token' => $token, 'username' => $user->nickname);
        $site  = urlencode(http_build_query($site));
        return $this->redirect("http://www.edusoho.com/question?site=".$site."");
    }

    public function inspectAction(Request $request)
    {
        $inspectList = array(
            $this->addInspectRole('host', $this->hostInspect($request))
        );
        $inspectList = array_filter($inspectList);
        return $this->render('TopxiaAdminBundle:Default:inspect.html.twig', array(
            'inspectList' => $inspectList
        ));
    }

    private function addInspectRole($name, $value)
    {
        if ($value['status'] == 'ok') {
            return array();
        }

        return array('name' => $name, 'value' => $value);
    }

    private function hostInspect($request)
    {
        $currentHost        = $request->server->get('HTTP_HOST');
        $siteSetting        = $this->getSettingService()->get('site');
        $settingUrl         = $this->generateUrl('admin_setting_site');
        $filter             = array('http://', 'https://');
        $siteSetting['url'] = rtrim($siteSetting['url']);
        $siteSetting['url'] = rtrim($siteSetting['url'], '/');

        if ($currentHost != str_replace($filter, "", $siteSetting['url'])) {
            return array(
                'status'       => 'warning',
                'errorMessage' => $this->getServiceKernel()->trans('当前域名和设置域名不符，为避免影响云短信功能的正常使用，请到【系统】-【站点设置】-【基础信息】-【网站域名】'),
                'except'       => $siteSetting['url'],
                'actually'     => $currentHost,
                'settingUrl'   => $settingUrl
            );
        }

        return array('status' => 'ok', 'except' => $siteSetting['url'], 'actually' => $currentHost, 'settingUrl' => $settingUrl);
    }

    public function getCloudNoticesAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            $domain = $this->generateUrl('homepage', array(), true);
            $api    = CloudAPIFactory::create('root');
            $result = $api->get('/trial/remainDays', array('domain' => $domain));

            return $this->render('TopxiaAdminBundle:Default:cloud-notice.html.twig', array(
                "trialTime" => (isset($result)) ? $result : null
            ));
        } elseif ($this->getWebExtension()->isWithoutNetwork()) {
            $notices = array();
        } else {
            $notices = $this->getNoticesFromOpen();
        }

        return $this->render('TopxiaAdminBundle:Default:cloud-notice.html.twig', array(
            "notices" => $notices
        ));
    }

    private function getNoticesFromOpen()
    {
        $url = "http://open.edusoho.com/api/v1/context/notice";
        return CurlToolkit::request('GET', $url);
    }

    public function officialMessagesAction()
    {
        $message = $this->getAppService()->getMessages();

        return $this->render('TopxiaAdminBundle:Default:official.messages.html.twig', array(
            "message" => $message
        ));
    }

    public function systemStatusAction()
    {
        $apps          = array();
        $systemVersion = "";
        $error         = "";

        $apps = $this->getAppService()->checkAppUpgrades();

        $appCount = count($apps);

        if (isset($apps['error'])) {
            $error = "error";
        }

        $mainAppUpgrade = null;
        foreach ($apps as $key => $value) {
            if (isset($value['code']) && $value['code'] == "MAIN") {
                $mainAppUpgrade = $value;
            }
        }

        $api              = CloudAPIFactory::create('leaf');
        $liveCourseStatus = $api->get('/lives/account');

        $rootApi             = CloudAPIFactory::create('root');
        $mobileCustomization = $rootApi->get('/customization/mobile/info');
        $info                = $rootApi->get('/me');


        $cloudServiceCount = 0;

        $storageSetting = $this->getSettingService()->get('storage');
        if (empty($storageSetting['upload_mode']) || $storageSetting['upload_mode'] != 'cloud') {
            $cloudServiceCount += 2;
        }
        $cloudSms = $this->getSettingService()->get('course');
        if (empty($cloudSms['live_course_enabled'])) {
            $cloudServiceCount += 1;
        }

        $cloudSms = $this->getSettingService()->get('cloud_sms');
        if (empty($cloudSms['sms_enabled'])) {
            $cloudServiceCount += 1;
        }

        $cloudSearch = $this->getSettingService()->get('cloud_search');
        if (empty($cloudSearch['search_enabled'])) {
            $cloudServiceCount += 1;
        }


        return $this->render('TopxiaAdminBundle:Default:system.status.html.twig', array(
            "info"                => $info,
            "apps"                => $apps,
            "error"               => $error,
            "mainAppUpgrade"      => $mainAppUpgrade,
            "app_count"           => $appCount,
            "liveCourseStatus"    => $liveCourseStatus,
            "mobileCustomization" => $mobileCustomization,
            'cloudServiceCount'   => $cloudServiceCount
        ));
    }

    public function latestUsersBlockAction(Request $request)
    {
        $users = $this->getCourseService()->searchMemberCountGroupByFields($conditions, $groupBy);
        return $this->render('TopxiaAdminBundle:Default:latest-users-block.html.twig', array(
            'users' => $users
        ));
    }

    public function userCoinsRecordsBlockAction(Request $request)
    {
        $userIds = $this->getCashService()->findUserIdsByFlows("outflow", "", "DESC", 0, 5);

        $userIds = ArrayToolkit::column($userIds, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaAdminBundle:Default:user-coins-block.html.twig', array(
            'userIds' => $userIds,
            'users'   => $users
        ));
    }

    public function operationAnalysisDashbordBlockAction(Request $request)
    {
        $todayTimeStart = strtotime(date("Y-m-d", time()));
        $todayTimeEnd   = strtotime(date("Y-m-d", time() + 24 * 3600));


        $onlineCount = $this->getStatisticsService()->getOnlineCount(15 * 60);
        $loginCount  = $this->getStatisticsService()->getloginCount(15 * 60);

        $todayRegisterNum = $this->getUserService()->searchUserCount(array("startTime" => $todayTimeStart, "endTime" => $todayTimeEnd));
        $totalRegisterNum = $this->getUserService()->searchUserCount(array());

        $todayCourseMemberNum = $this->getOrderService()->searchOrderCount(array("paidStartTime" => $todayTimeStart, "paidEndTime" => $todayTimeEnd, "targetType" => 'course', "status" => "paid"));
        $totalCourseMemberNum = $this->getOrderService()->searchOrderCount(array("targetType" => 'course', "status" => "paid"));

        $todayClassroomMemberNum = $this->getOrderService()->searchOrderCount(array("paidStartTime" => $todayTimeStart, "paidEndTime" => $todayTimeEnd, "targetType" => 'classroom', "status" => "paid"));
        $totalClassroomMemberNum = $this->getOrderService()->searchOrderCount(array("targetType" => 'classroom', "status" => "paid"));

        $todayVipNum = $this->getOrderService()->searchOrderCount(array("paidStartTime" => $todayTimeStart, "paidEndTime" => $todayTimeEnd, "targetType" => 'vip', "status" => "paid"));
        $totalVipNum = $this->getOrderService()->searchOrderCount(array("targetType" => 'vip', "status" => "paid"));


        $todayThreadNum         = $this->getThreadService()->searchThreadCount(array('startCreatedTime' => $todayTimeStart, 'endCreatedTime' => $todayTimeEnd, 'postNumLargerThan' => 0));
        $todayThreadUnAnswerNum = $this->getThreadService()->searchThreadCount(array('startCreatedTime' => $todayTimeStart, 'endCreatedTime' => $todayTimeEnd, 'postNum' => 0));
        $totalThreadNum         = $this->getThreadService()->searchThreadCount(array());

        $publishedCourseNum = $this->getCourseService()->searchCourseCount(array("status" => 'published'));
        $totalCourseNum     = $this->getCourseService()->searchCourseCount(array());


        $publishedClassroomNum = $this->getClassroomService()->searchClassroomsCount(array('status' => 'published'));
        $totalClassroomNum     = $this->getClassroomService()->searchClassroomsCount(array());

        return $this->render('TopxiaAdminBundle:Default:operation-analysis-dashbord.html.twig', array(
            'onlineCount' => $onlineCount,
            'loginCount'  => $loginCount,

            'todayRegisterNum' => $todayRegisterNum,
            'totalRegisterNum' => $totalRegisterNum,

            'todayCourseMemberNum' => $todayCourseMemberNum,
            'totalCourseMemberNum' => $totalCourseMemberNum,

            'todayClassroomMemberNum' => $todayClassroomMemberNum,
            'totalClassroomMemberNum' => $totalClassroomMemberNum,

            'todayVipNum' => $todayVipNum,
            'totalVipNum' => $totalVipNum,

            'todayThreadNum'         => $todayThreadNum,
            'todayThreadUnAnswerNum' => $todayThreadUnAnswerNum,
            'totalThreadNum'         => $totalThreadNum,

            'publishedCourseNum' => $publishedCourseNum,
            'totalCourseNum'     => $totalCourseNum,

            'publishedClassroomNum' => $publishedClassroomNum,
            'totalClassroomNum'     => $totalClassroomNum

        ));
    }


    public function userStatisticAction(Request $request, $period)
    {
        $userStatistic = array();

        $days        = $this->getDaysDiff($period);
        $active_days = "30";

        //x轴显示日期
        $xAxisDate = $this->generateDateRange($days, 'Y-m-d');

        $userStatistic['date'] = $xAxisDate;

        //用于填充的空模板数据
        foreach ($xAxisDate as $date) {
            $zeroAnalysis[$date] = array('count' => 0, 'date' => $date);
        }
        //每日注册用户
        $timeRange        = $this->getTimeRange($period);
        $analysisRegister = $this->getUserService()->analysisRegisterDataByTime($timeRange['startTime'], $timeRange['endTime']);
        $analysisRegister = ArrayToolkit::index($analysisRegister, 'date');
        $analysisRegister = array_merge($zeroAnalysis, $analysisRegister);

        $userStatistic['register'] = $this->array_value_recursive('count', $analysisRegister);

        //活跃用户
        $activeAnalysis          = $this->getUserActiveService()->analysisActiveUser(strtotime(date('Y-m-d', time() - ($days + $active_days) * 24 * 60 * 60)), strtotime(date('Y-m-d', time() + 24 * 60 * 60)));
        $activeAnalysis          = $this->fillActiveUserCount($xAxisDate, $activeAnalysis);
        $userStatistic['active'] = $activeAnalysis;

        //每日注册总数
        $dayRegisterTotal = $this->getUserService()->analysisUserSumByTime($timeRange['endTime']);
        $dayRegisterTotal = $this->fillAnalysisUserSum($timeRange, $dayRegisterTotal);
        $dayRegisterTotal = $this->array_value_recursive('count', $dayRegisterTotal);

        //流失用户
        $unActiveAnalysis = array();
        array_walk($dayRegisterTotal, function ($value, $index) use (&$unActiveAnalysis, $activeAnalysis) {
            //每日注册总数-每日活跃用户
            array_push($unActiveAnalysis, ($value - $activeAnalysis[$index]));
        });

        $userStatistic['unActive'] = $unActiveAnalysis;

        return $this->createJsonResponse($userStatistic);
    }

    public function lessonLearnStatisticAction(Request $request, $period)
    {
        //最近七天，最近三十天
        $days = $this->getDaysDiff($period);
        for ($i = $days; $i >= 0; $i--) {
            $dates[]             = date('y/m/d', time() - $i * 24 * 60 * 60);
            $date                = date('Y-m-d', time() - $i * 24 * 60 * 60);
            $defaultDatas[$date] = array('count' => 0, 'date' => $date);
        }

        $timeRange = $this->getTimeRange($period);

        $finishedLessonData = $this->getCourseService()->analysisLessonFinishedDataByTime($timeRange['startTime'], $timeRange['endTime']);

        $finishedLessonData = ArrayToolkit::index($finishedLessonData, 'date');
        $finishedLessonData = array_merge($defaultDatas, $finishedLessonData);

        return $this->createJsonResponse(array(
            'date' => $dates,
            'data' => $this->array_value_recursive('count', $finishedLessonData),
        ));
    }

    /**
     * @param Request $request
     * @param $period
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * 学习人次
     */
    public function studyStatisticAction(Request $request, $period)
    {
        $days = $this->getDaysDiff($period);
        for ($i = $days; $i >= 0; $i--) {
            $dates[]             = date('y/m/d', time() - $i * 24 * 60 * 60);
            $date                = date('Y-m-d', time() - $i * 24 * 60 * 60);
            $defaultDatas[$date] = array('count' => 0, 'date' => $date);
        }

        $timeRange  = $this->getTimeRange($period);
        $conditions = array('paidStartTime' => $timeRange['startTime'], 'paidEndTime' => $timeRange['endTime'], 'status' => 'paid');
        $newOrders  = $this->getOrderService()->analysisOrderDate($conditions);
        $newOrders  = ArrayToolkit::index($newOrders, 'date');
        $newOrders  = array_merge($defaultDatas, $newOrders);

        $conditions    = array('paidStartTime' => $timeRange['startTime'], 'paidEndTime' => $timeRange['endTime'], 'status' => 'paid', 'totalPriceGreaterThan' => 0);
        $newPaidOrders = $this->getOrderService()->analysisOrderDate($conditions);
        $newPaidOrders = ArrayToolkit::index($newPaidOrders, 'date');
        $newPaidOrders = array_merge($defaultDatas, $newPaidOrders);

        return $this->createJsonResponse(array(
            'date'    => $dates,
            'new'     => $this->array_value_recursive('count', $newOrders),
            'feePaid' => $this->array_value_recursive('count', $newPaidOrders)
        ));

    }

    function array_value_recursive($key, array $arr)
    {
        $val = array();
        array_walk_recursive($arr, function ($v, $k) use ($key, &$val) {
            if ($k == $key) array_push($val, intval($v));
        });
        return count($val) > 1 ? $val : array_pop($val);
    }

    public function orderStatisticAction(Request $request, $period)
    {

        $days = $this->getDaysDiff($period);

        $startTime = strtotime(date('Y-m-d', time() - $days * 24 * 60 * 60));

        $orderDatas = $this->getOrderService()->analysisPaidOrderGroupByTargetType($startTime, 'targetType');

        $defaults   = array(
            'course'    => array('targetType' => 'course', 'value' => 0),
            'vip'       => array('targetType' => 'vip', 'value' => 0),
            'classroom' => array('targetType' => 'classroom', 'value' => 0)
        );
        $orderDatas = ArrayToolkit::index($orderDatas, 'targetType');
        $orderDatas = array_merge($defaults, $orderDatas);

        $names = array('course' => '课程订单', 'vip' => '会员订单', 'classroom' => '班级订单');
        array_walk($orderDatas, function (&$orderData) use ($names) {
            $orderData['name'] = $names[$orderData['targetType']];
            unset($orderData['targetType']);
        });
        return $this->createJsonResponse(array_values($orderDatas));

    }

    public function courseExploreAction(Request $request, $period)
    {
        $days      = $this->getDaysDiff($period);
        $startTime = strtotime(date('Y-m-d', time() - $days * 24 * 60 * 60));

        $memberCounts = $this->getCourseService()->searchMemberCountGroupByFields(array('startTimeGreaterThan' => $startTime, 'classroomId' => 0), 'courseId', 0, 10);
        $courseIds    = ArrayToolkit::column($memberCounts, 'courseId');
        $courses      = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses      = ArrayToolkit::index($courses, 'id');

        return $this->render('TopxiaAdminBundle:Default/Parts:course-explore-table.html.twig', array(
            'memberCounts' => $memberCounts,
            'courses'      => $courses
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
        return $this->render('TopxiaAdminBundle:Default/Parts:course-review-table.html.twig', array(
            'reviews' => $reviews
        ));
    }

    public function unsolvedQuestionsBlockAction(Request $request)
    {
        $questions = $this->getThreadService()->searchThreads(array('type' => 'question'), 'createdNotStick', 0, 5);

        $unPostedQuestion = array();

        foreach ($questions as $key => $value) {
            if ($value['postNum'] == 0) {
                $unPostedQuestion[] = $value;
            } else {
                $threadPostsNum = $this->getThreadService()->getThreadPostCountByThreadId($value['id']);
                $userPostsNum   = $this->getThreadService()->getPostCountByuserIdAndThreadId($value['userId'], $value['id']);

                if ($userPostsNum == $threadPostsNum) {
                    $unPostedQuestion[] = $value;
                }
            }
        }

        $questions = $unPostedQuestion;

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($questions, 'courseId'));
        $askers  = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId'));

        $teacherIds = array();

        foreach (ArrayToolkit::column($courses, 'teacherIds') as $teacherId) {
            $teacherIds = array_merge($teacherIds, $teacherId);
        }

        $teachers = $this->getUserService()->findUsersByIds($teacherIds);

        return $this->render('TopxiaAdminBundle:Default:unsolved-questions-block.html.twig', array(
            'questions' => $questions,
            'courses'   => $courses,
            'askers'    => $askers,
            'teachers'  => $teachers
        ));
    }

    public function latestPaidOrdersBlockAction(Request $request)
    {
        $orders = $this->getOrderService()->searchOrders(array('status' => 'paid'), 'latest', 0, 5);
        $users  = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orders, 'userId'));

        return $this->render('TopxiaAdminBundle:Default:latest-paid-orders-block.html.twig', array(
            'orders' => $orders,
            'users'  => $users
        ));
    }

    public function questionRemindTeachersAction(Request $request, $courseId, $questionId)
    {
        $course   = $this->getCourseService()->getCourse($courseId);
        $question = $this->getThreadService()->getThread($courseId, $questionId);

        $message = array(
            'courseTitle'   => $course['title'],
            'courseId'      => $course['id'],
            'threadId'      => $question['id'],
            'questionTitle' => strip_tags($question['title'])
        );

        foreach ($course['teacherIds'] as $receiverId) {
            $result = $this->getNotificationService()->notify($receiverId, 'questionRemind', $message);
        }

        return $this->createJsonResponse(array('success' => true, 'message' => 'ok'));
    }

    private function getToken()
    {
        $site = $this->getSettingService()->get('site');
        return 'token_'.date('Ymd', time()).$site['url'];
    }

    public function weekday($time)
    {
        if (is_numeric($time)) {
            $weekday = array($this->getServiceKernel()->trans('星期日'), $this->getServiceKernel()->trans('星期一'), $this->getServiceKernel()->trans('星期二'), $this->getServiceKernel()->trans('星期三'), $this->getServiceKernel()->trans('星期四'), $this->getServiceKernel()->trans('星期五'), $this->getServiceKernel()->trans('星期六'));
            return $weekday[date('w', $time)];
        }

        return false;
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    /**
     * @return StatisticsServiceImpl
     */
    protected function getStatisticsService()
    {
        return $this->getServiceKernel()->createService('System.StatisticsService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    /**
     * @return CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    /**
     * @return OrderServiceImpl
     */
    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getCashService()
    {
        return $this->getServiceKernel()->createService('Cash.CashService');
    }

    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

    protected function getUpgradeNoticeService()
    {
        return $this->getServiceKernel()->createService('User.UpgradeNoticeService');
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }


    private function getDaysDiff($period)
    {
        $days = $period == 'week' ? 6 : 29;
        return $days;
    }

    protected function getTimeRange($period)
    {
        $days = $this->getDaysDiff($period);

        return array('startTime' => strtotime(date('Y-m-d', time() - $days * 24 * 60 * 60)), 'endTime' => strtotime(date('Y-m-d', time() + 24 * 3600)));
    }

    protected function makeDateRange($startTime, $endTime)
    {
        $dates = array();

        $currentTime = $startTime;

        while (true) {
            if ($currentTime >= $endTime) {
                break;
            }

            $currentDate = date('Y-m-d', $currentTime);
            $dates[]     = $currentDate;

            $currentTime = $currentTime + 3600 * 24;
        }

        return $dates;
    }

    protected function generateDateRange($days, $format = 'Y/m/d')
    {
        $dates = array();
        for ($i = $days; $i >= 0; $i--) {
            $dates[] = date($format, time() - $i * 24 * 60 * 60);
        }
        return $dates;
    }

    protected function fillActiveUserCount($xAxisDate, $activeAnalysis)
    {
        $result = array();
        array_walk($xAxisDate, function ($date) use ($activeAnalysis, &$result) {
            foreach ($activeAnalysis as $index => $value) {
                //在30天内登录过系统的用户即为活跃用户
                $diff = (strtotime($date) - strtotime($value['date'])) / 86400;
                if ($diff >= 0 && $diff <= 30) {
                    $result[$date][] = $value['userId'];
                }
            }
            if (empty($result[$date])) {
                $result[$date] = array();
            }
        });

        array_walk($result, function (&$data) {
            $data = count(array_unique($data));
        });
        return array_values($result);
    }

    //获取每天的注册总数
    protected function fillAnalysisUserSum($timeRange, $currentData)
    {
        $dates       = $this->makeDateRange($timeRange['startTime'], $timeRange['endTime']);
        $userSumData = $currentData;

        $currentData = ArrayToolkit::index($currentData, 'date');

        foreach ($dates as $key => $value) {
            $zeroData[] = array("date" => $value, "count" => 0);
        }

        if ($userSumData) {
            $countTmp = $userSumData[0]["count"];

            //  var_dump($userSumData);
            foreach ($zeroData as $key => $value) {
                foreach ($userSumData as $userKey => $val) {
                    //   var_dump($userKey, $value['date'], $val['date']);
                    if ($userKey != 0 && ($value['date'] < $val['date']) && isset($userSumData[($userKey + 1)]) && $value['date'] > $userSumData[($userKey + 1)]['date']) {
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

        return $zeroData;
    }

}
