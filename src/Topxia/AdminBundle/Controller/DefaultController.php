<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Common\CurlToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends BaseController
{
    public function popularCoursesAction(Request $request)
    {
        $dateType   = $request->query->get('dateType');
        $currentDay = $this->weekday(time());

        if ($dateType == "today") {
            $startTime = strtotime('today');
            $endTime   = strtotime('tomorrow');
        }

        if ($dateType == "yesterday") {
            $startTime = strtotime('yesterday');
            $endTime   = strtotime('today');
        }

        if ($dateType == "this_week") {
            if ($currentDay == '星期日') {
                $startTime = strtotime('Monday last week');
                $endTime   = strtotime('Monday this week');
            } else {
                $startTime = strtotime('Monday this week');
                $endTime   = strtotime('Monday next week');
            }
        }

        if ($dateType == "last_week") {
            if ($currentDay == '星期日') {
                $startTime = strtotime('Monday last week') - (7 * 24 * 60 * 60);
                $endTime   = strtotime('Monday this week') - (7 * 24 * 60 * 60);
            } else {
                $startTime = strtotime('Monday last week');
                $endTime   = strtotime('Monday this week');
            }
        }

        if ($dateType == "this_month") {
            $startTime = strtotime('first day of this month midnight');
            $endTime   = strtotime('first day of next month midnight');
        }

        if ($dateType == "last_month") {
            $startTime = strtotime('first day of last month midnight');
            $endTime   = strtotime('first day of this month midnight');
        }

        $members   = $this->getCourseService()->countMembersByStartTimeAndEndTime($startTime, $endTime);
        $courseIds = ArrayToolkit::column($members, "courseId");

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, "id");

        $sortedCourses = array();

        $orders = $this->getOrderService()->sumOrderAmounts($startTime, $endTime, $courseIds);
        $orders = ArrayToolkit::index($orders, "targetId");

        foreach ($members as $key => $value) {
            $course                    = array();
            $course['title']           = $courses[$value["courseId"]]['title'];
            $course['courseId']        = $courses[$value["courseId"]]['id'];
            $course['addedStudentNum'] = $value['co'];
            $course['studentNum']      = $courses[$value["courseId"]]['studentNum'];

            if (isset($orders[$value["courseId"]])) {
                $course['addedMoney'] = $orders[$value["courseId"]]['amount'];
            } else {
                $course['addedMoney'] = 0;
            }

            $sortedCourses[] = $course;
        }

        return $this->render('TopxiaAdminBundle:Default:popular-courses-table.html.twig', array(
            'sortedCourses' => $sortedCourses
        ));
    }

    public function indexAction(Request $request)
    {
        return $this->render('TopxiaAdminBundle:Default:index.html.twig');
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
                'errorMessage' => '当前域名和设置域名不符，为避免影响云短信、云搜索功能的正常使用，请到【系统】-【站点设置】-【基础信息】-【网站域名】',
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
        $apps          = $this->getAppService()->checkAppUpgrades();

        $appsAll = $this->getAppService()->getCenterApps();

        $codes = ArrayToolkit::column($appsAll, 'code');

        $installedApps = $this->getAppService()->findAppsByCodes($codes);

        $unInstallAppCount = count($appsAll) - count($installedApps);

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

        return $this->render('TopxiaAdminBundle:Default:system.status.html.twig', array(
            "apps"                => $apps,
            "error"               => $error,
            "mainAppUpgrade"      => $mainAppUpgrade,
            "app_count"           => $appCount,
            "unInstallAppCount"   => $unInstallAppCount,
            "liveCourseStatus"    => $liveCourseStatus,
            "mobileCustomization" => $mobileCustomization
        ));
    }

    public function latestUsersBlockAction(Request $request)
    {
        $users = $this->getUserService()->searchUsers(array(), array('createdTime', 'DESC'), 0, 5);
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

        $yesterdayTimeStart = strtotime(date("Y-m-d", time() - 24 * 3600));
        $yesterdayTimeEnd   = strtotime(date("Y-m-d", time()));

        $todayRegisterNum     = $this->getUserService()->searchUserCount(array("startTime" => $todayTimeStart, "endTime" => $todayTimeEnd));
        $yesterdayRegisterNum = $this->getUserService()->searchUserCount(array("startTime" => $yesterdayTimeStart, "endTime" => $yesterdayTimeEnd));

        $todayUserSum     = $this->getUserService()->findUsersCountByLessThanCreatedTime(strtotime(date("Y-m-d", time() + 24 * 3600)));
        $yesterdayUserSum = $this->getUserService()->findUsersCountByLessThanCreatedTime(strtotime(date("Y-m-d", time())));

        $todayLoginNum     = $this->getLogService()->analysisLoginNumByTime(strtotime(date("Y-m-d", time())), strtotime(date("Y-m-d", time() + 24 * 3600)));
        $yesterdayLoginNum = $this->getLogService()->analysisLoginNumByTime(strtotime(date("Y-m-d", time() - 24 * 3600)), strtotime(date("Y-m-d", time())));

        $todayCourseNum     = $this->getCourseService()->searchCourseCount(array("startTime" => $todayTimeStart, "endTime" => $todayTimeEnd));
        $yesterdayCourseNum = $this->getCourseService()->searchCourseCount(array("startTime" => $yesterdayTimeStart, "endTime" => $yesterdayTimeEnd));

        $todayCourseSum     = $this->getCourseService()->findCoursesCountByLessThanCreatedTime(strtotime(date("Y-m-d", time() + 24 * 3600)));
        $yesterdayCourseSum = $this->getCourseService()->findCoursesCountByLessThanCreatedTime(strtotime(date("Y-m-d", time())));

        $todayLessonNum = $this->getCourseService()->searchLessonCount(array("startTime" => $todayTimeStart, "endTime" => $todayTimeEnd));

        $yesterdayLessonNum = $this->getCourseService()->searchLessonCount(array("startTime" => $yesterdayTimeStart, "endTime" => $yesterdayTimeEnd));

        $todayJoinLessonNum = $this->getOrderService()->searchOrderCount(array("paidStartTime" => $todayTimeStart, "paidEndTime" => $todayTimeEnd, "status" => "paid"));

        $yesterdayJoinLessonNum = $this->getOrderService()->searchOrderCount(array("paidStartTime" => $yesterdayTimeStart, "paidEndTime" => $yesterdayTimeEnd, "status" => "paid"));

        $todayBuyLessonNum = $this->getOrderService()->searchOrderCount(array("paidStartTime" => $todayTimeStart, "paidEndTime" => $todayTimeEnd, "status" => "paid", "amount" => "0.00", "targetType" => 'course'));

        $yesterdayBuyLessonNum = $this->getOrderService()->searchOrderCount(array("paidStartTime" => $yesterdayTimeStart, "paidEndTime" => $yesterdayTimeEnd, "status" => "paid", "amount" => "0.00", "targetType" => 'course'));

        $todayBuyClassroomNum = $this->getOrderService()->searchOrderCount(array("paidStartTime" => $todayTimeStart, "paidEndTime" => $todayTimeEnd, "status" => "paid", "amount" => "0.00", "targetType" => 'classroom'));

        $yesterdayBuyClassroomNum = $this->getOrderService()->searchOrderCount(array("paidStartTime" => $yesterdayTimeStart, "paidEndTime" => $yesterdayTimeEnd, "status" => "paid", "amount" => "0.00", "targetType" => 'classroom'));

        $todayFinishedLessonNum = $this->getCourseService()->searchLearnCount(array("startTime" => $todayTimeStart, "endTime" => $todayTimeEnd, "status" => "finished"));

        $yesterdayFinishedLessonNum = $this->getCourseService()->searchLearnCount(array("startTime" => $yesterdayTimeStart, "endTime" => $yesterdayTimeEnd, "status" => "finished"));

        $todayAllVideoViewedNum = $this->getCourseService()->searchAnalysisLessonViewCount(array('startTime' => strtotime(date("Y-m-d", time())), 'endTime' => strtotime(date("Y-m-d", time() + 24 * 3600)), "fileType" => 'video'));

        $yesterdayAllVideoViewedNum = $this->getCourseService()->searchAnalysisLessonViewCount(array('startTime' => strtotime(date("Y-m-d", time() - 24 * 3600)), 'endTime' => strtotime(date("Y-m-d", time())), "fileType" => 'video'));

        $todayCloudVideoViewedNum = $this->getCourseService()->searchAnalysisLessonViewCount(array('startTime' => strtotime(date("Y-m-d", time())), 'endTime' => strtotime(date("Y-m-d", time() + 24 * 3600)), "fileType" => 'video', 'fileStorage' => 'cloud'));

        $yesterdayCloudVideoViewedNum = $this->getCourseService()->searchAnalysisLessonViewCount(array('startTime' => strtotime(date("Y-m-d", time() - 24 * 3600)), 'endTime' => strtotime(date("Y-m-d", time())), "fileType" => 'video', 'fileStorage' => 'cloud'));

        $todayLocalVideoViewedNum = $this->getCourseService()->searchAnalysisLessonViewCount(array('startTime' => strtotime(date("Y-m-d", time())), 'endTime' => strtotime(date("Y-m-d", time() + 24 * 3600)), "fileType" => 'video', 'fileStorage' => 'local'));

        $yesterdayLocalVideoViewedNum = $this->getCourseService()->searchAnalysisLessonViewCount(array('startTime' => strtotime(date("Y-m-d", time() - 24 * 3600)), 'endTime' => strtotime(date("Y-m-d", time())), "fileType" => 'video', 'fileStorage' => 'local'));

        $todayNetVideoViewedNum = $this->getCourseService()->searchAnalysisLessonViewCount(array('startTime' => strtotime(date("Y-m-d", time())), 'endTime' => strtotime(date("Y-m-d", time() + 24 * 3600)), "fileType" => 'video', 'fileStorage' => 'net'));

        $yesterdayNetVideoViewedNum = $this->getCourseService()->searchAnalysisLessonViewCount(array('startTime' => strtotime(date("Y-m-d", time() - 24 * 3600)), 'endTime' => strtotime(date("Y-m-d", time())), "fileType" => 'video', 'fileStorage' => 'net'));

        $todayExitLessonNum = $this->getOrderService()->searchOrderCount(array("paidStartTime" => $todayTimeStart, "paidEndTime" => $todayTimeEnd, "statusPaid" => "paid", "statusCreated" => "created"));

        $yesterdayExitLessonNum = $this->getOrderService()->searchOrderCount(array("paidStartTime" => $yesterdayTimeStart, "paidEndTime" => $yesterdayTimeEnd, "statusPaid" => "paid", "statusCreated" => "created"));

        $todayIncome = $this->getOrderService()->analysisAmount(array("paidStartTime" => strtotime(date("Y-m-d", time())), "paidEndTime" => strtotime(date("Y-m-d", time() + 24 * 3600)), "status" => "paid")) + 0.00;

        $yesterdayIncome = $this->getOrderService()->analysisAmount(array("paidStartTime" => strtotime(date("Y-m-d", time() - 24 * 3600)), "paidEndTime" => strtotime(date("Y-m-d", time())), "status" => "paid")) + 0.00;

        $todayCourseIncome = $this->getOrderService()->analysisAmount(array("paidStartTime" => strtotime(date("Y-m-d", time())), "paidEndTime" => strtotime(date("Y-m-d", time() + 24 * 3600)), "status" => "paid", "targetType" => "course")) + 0.00;

        $yesterdayCourseIncome = $this->getOrderService()->analysisAmount(array("paidStartTime" => strtotime(date("Y-m-d", time() - 24 * 3600)), "paidEndTime" => strtotime(date("Y-m-d", time())), "status" => "paid", "targetType" => "course")) + 0.00;

        $todayClassroomIncome = $this->getOrderService()->analysisAmount(array("paidStartTime" => strtotime(date("Y-m-d", time())), "paidEndTime" => strtotime(date("Y-m-d", time() + 24 * 3600)), "status" => "paid", "targetType" => "classroom")) + 0.00;

        $yesterdayClassroomIncome = $this->getOrderService()->analysisAmount(array("paidStartTime" => strtotime(date("Y-m-d", time() - 24 * 3600)), "paidEndTime" => strtotime(date("Y-m-d", time())), "status" => "paid", "targetType" => "classroom")) + 0.00;

        $todayVipIncome = $this->getOrderService()->analysisAmount(array("paidStartTime" => strtotime(date("Y-m-d", time())), "paidEndTime" => strtotime(date("Y-m-d", time() + 24 * 3600)), "status" => "paid", "targetType" => "vip")) + 0.00;

        $yesterdayVipIncome = $this->getOrderService()->analysisAmount(array("paidStartTime" => strtotime(date("Y-m-d", time() - 24 * 3600)), "paidEndTime" => strtotime(date("Y-m-d", time())), "status" => "paid", "targetType" => "vip")) + 0.00;

        $storageSetting = $this->getSettingService()->get('storage');

        if (!empty($storageSetting['cloud_access_key']) && !empty($storageSetting['cloud_secret_key'])) {
            $factory        = new CloudClientFactory();
            $client         = $factory->createClient($storageSetting);
            $keyCheckResult = $client->checkKey();
        } else {
            $keyCheckResult = array('error' => 'error');
        }

        return $this->render('TopxiaAdminBundle:Default:operation-analysis-dashbord.html.twig', array(
            'todayUserSum'                 => $todayUserSum,
            'yesterdayUserSum'             => $yesterdayUserSum,
            'todayCourseSum'               => $todayCourseSum,
            'yesterdayCourseSum'           => $yesterdayCourseSum,
            'todayRegisterNum'             => $todayRegisterNum,
            'yesterdayRegisterNum'         => $yesterdayRegisterNum,
            'todayLoginNum'                => $todayLoginNum,
            'yesterdayLoginNum'            => $yesterdayLoginNum,
            'todayCourseNum'               => $todayCourseNum,
            'yesterdayCourseNum'           => $yesterdayCourseNum,
            'todayLessonNum'               => $todayLessonNum,
            'yesterdayLessonNum'           => $yesterdayLessonNum,
            'todayJoinLessonNum'           => $todayJoinLessonNum,
            'yesterdayJoinLessonNum'       => $yesterdayJoinLessonNum,
            'todayBuyLessonNum'            => $todayBuyLessonNum,
            'yesterdayBuyLessonNum'        => $yesterdayBuyLessonNum,

            'todayBuyClassroomNum'         => $todayBuyClassroomNum,
            'yesterdayBuyClassroomNum'     => $yesterdayBuyClassroomNum,

            'todayFinishedLessonNum'       => $todayFinishedLessonNum,
            'yesterdayFinishedLessonNum'   => $yesterdayFinishedLessonNum,

            'todayAllVideoViewedNum'       => $todayAllVideoViewedNum,
            'yesterdayAllVideoViewedNum'   => $yesterdayAllVideoViewedNum,

            'todayCloudVideoViewedNum'     => $todayCloudVideoViewedNum,
            'yesterdayCloudVideoViewedNum' => $yesterdayCloudVideoViewedNum,

            'todayLocalVideoViewedNum'     => $todayLocalVideoViewedNum,
            'yesterdayLocalVideoViewedNum' => $yesterdayLocalVideoViewedNum,

            'todayNetVideoViewedNum'       => $todayNetVideoViewedNum,
            'yesterdayNetVideoViewedNum'   => $yesterdayNetVideoViewedNum,

            'todayIncome'                  => $todayIncome,
            'yesterdayIncome'              => $yesterdayIncome,
            'todayCourseIncome'            => $todayCourseIncome,
            'yesterdayCourseIncome'        => $yesterdayCourseIncome,
            'todayClassroomIncome'         => $todayClassroomIncome,
            'yesterdayClassroomIncome'     => $yesterdayClassroomIncome,
            'todayVipIncome'               => $todayVipIncome,
            'yesterdayVipIncome'           => $yesterdayVipIncome,
            'todayExitLessonNum'           => $todayExitLessonNum,
            'yesterdayExitLessonNum'       => $yesterdayExitLessonNum,
            'keyCheckResult'               => $keyCheckResult
        ));
    }

    public function onlineCountAction(Request $request)
    {
        $onlineCount = $this->getStatisticsService()->getOnlineCount(15 * 60);
        return $this->createJsonResponse(array('onlineCount' => $onlineCount, 'message' => 'ok'));
    }

    public function loginCountAction(Request $request)
    {
        $loginCount = $this->getStatisticsService()->getloginCount(15 * 60);
        return $this->createJsonResponse(array('loginCount' => $loginCount, 'message' => 'ok'));
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
            $weekday = array('星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六');
            return $weekday[date('w', $time)];
        }

        return false;
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getStatisticsService()
    {
        return $this->getServiceKernel()->createService('System.StatisticsService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

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
}
