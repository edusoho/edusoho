<?php

namespace AppBundle\Controller\AdminV2;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\CurlToolkit;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\Service\AppService;
use Biz\Content\Service\BlockService;
use Biz\Content\Service\NavigationService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ThreadService;
use Biz\System\Service\SettingService;
use Biz\System\Service\StatisticsService;
use Biz\User\Service\NotificationService;
use Codeages\Biz\Order\Service\OrderService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class DefaultController extends BaseController
{
    public function indexAction(Request $request)
    {
        $weekAndMonthDate = array('weekDate' => date('Y-m-d', time() - 6 * 24 * 60 * 60), 'monthDate' => date('Y-m-d', time() - 29 * 24 * 60 * 60));

        return $this->render('admin-v2/default/index.html.twig', array(
            'dates' => $weekAndMonthDate,
            'newcomerTaskStatus' => $this->getNewcomerTaskStatus(),
        ));
    }

    public function questionRemindTeachersAction(Request $request, $courseId, $questionId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $question = $this->getThreadService()->getThread($courseId, $questionId);

        $message = array(
            'courseTitle' => $courseSet['title'],
            'courseId' => $course['id'],
            'threadId' => $question['id'],
            'questionTitle' => strip_tags($question['title']),
        );

        foreach ($course['teacherIds'] as $receiverId) {
            $result = $this->getNotificationService()->notify($receiverId, 'questionRemind', $message);
        }

        return $this->createJsonResponse(array('success' => true, 'message' => 'ok'));
    }

    public function statisticsDailyAction(Request $request)
    {
        $todayTimeStart = strtotime(date('Y-m-d', time()));
        $todayTimeEnd = strtotime(date('Y-m-d', time() + 24 * 3600));

        $loginCount = $this->getStatisticsService()->countLogin(time() - 15 * 60);
        $registerNum = $this->getUserService()->countUsers(array('startTime' => $todayTimeStart, 'endTime' => $todayTimeEnd));

        $conditions = array(
            'pay_time_GT' => $todayTimeStart,
            'pay_time_LT' => $todayTimeEnd,
            'statuses' => array('paid', 'success', 'finished', 'refunded'),
        );

        $newOrderCount = $this->getOrderService()->countOrders($conditions);
        $conditions['pay_amount_GT'] = 0;

        $newPaidOrderCount = $this->getOrderService()->countOrders($conditions);

        return $this->render('admin-v2/default/daily-statistics.html.twig', array(
            'loginCount' => $loginCount,
            'registerNum' => $registerNum,
            'newOrderCount' => $newOrderCount,
            'newPaidOrderCount' => $newPaidOrderCount,
        ));
    }

    public function feedbackAction(Request $request)
    {
        $site = $this->getSettingService()->get('site');
        $user = $this->getUser();
        $token = CurlToolkit::request('POST', 'http://www.edusoho.com/question/get/token', array());
        $site = array('name' => $site['name'], 'url' => $site['url'], 'token' => $token, 'username' => $user->nickname);
        $site = urlencode(http_build_query($site));

        return $this->redirect('http://www.edusoho.com/question?site='.$site.'');
    }

    public function validateDomainAction(Request $request)
    {
        $result = $this->domainInspect($request);

        if ('ok' == $result['status']) {
            return $this->render('admin-v2/default/domain.html.twig', array('inspectList' => array()));
        }

        return $this->render('admin-v2/default/domain.html.twig', array(
            'inspectList' => array('name' => 'host', 'value' => $result),
        ));
    }

    public function getCloudNoticesAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            $domain = $this->generateUrl('homepage', array(), true);
            $api = CloudAPIFactory::create('root');
            $result = $api->get('/trial/remainDays', array('domain' => $domain));
        }

        return $this->render('admin-v2/default/cloud-notice.html.twig', array(
            'trialTime' => (isset($result)) ? $result : null,
        ));
    }

    private function domainInspect($request)
    {
        $currentHost = $request->server->get('HTTP_HOST');
        $siteSetting = $this->getSettingService()->get('site');
        $settingUrl = $this->generateUrl('admin_v2_school_information');
        $filter = array('http://', 'https://');
        $siteSetting['url'] = rtrim($siteSetting['url']);
        $siteSetting['url'] = rtrim($siteSetting['url'], '/');

        if ($currentHost != str_replace($filter, '', $siteSetting['url'])) {
            return array(
                'status' => 'warning',
                'errorMessage' => ServiceKernel::instance()->trans('admin_v2.domain_error_hint'),
                'except' => $siteSetting['url'],
                'actually' => $currentHost,
                'settingUrl' => $settingUrl,
            );
        }

        return array('status' => 'ok', 'except' => $siteSetting['url'], 'actually' => $currentHost, 'settingUrl' => $settingUrl);
    }

    protected function getNewcomerTaskStatus()
    {
        $newcomerTaskStatus = array(
            'cloud_applied' => 0,
            'auth_applied' => 0,
            'payment_applied' => 0,
            'plugin_applied' => 0,
            'course_applied' => 0,
            'decoration_applied' => 0,
        );

        $storage = $this->getSettingService()->get('storage', array());
        if (!empty($storage['cloud_key_applied'])) {
            $newcomerTaskStatus['cloud_applied'] = 1;
        }
        $payment = $this->getSettingService()->get('payment', array());
        if (!empty($payment['alipay_enabled']) || !empty($payment['wxpay_enabled']) || !empty($payment['llpay_enabled'])) {
            $newcomerTaskStatus['payment_applied']= 1;
        }
        $apps = $this->getAppService()->findApps(0, $this->getAppService()->findAppCount());
        $appTypes = ArrayToolkit::column($apps, 'type');
        if (in_array(AppService::PLUGIN_TYPE, $appTypes)) {
            $newcomerTaskStatus['plugin_applied'] = 1;
        }
        $publishCount = $this->getCourseSetService()->countCourseSets(array('status' => 'published'));
        if (!empty($publishCount)) {
            $newcomerTaskStatus['course_applied'] = 1;
        }

        $newcomerTaskSetting = $this->getSettingService()->get('newcomer_task', $newcomerTaskStatus);
        $newcomerTaskSetting = array_filter($newcomerTaskSetting);
        $newcomerTaskStatus = array_merge($newcomerTaskStatus, $newcomerTaskSetting);
        $this->getSettingService()->set('newcomer_task', array_filter($newcomerTaskStatus));

        return $newcomerTaskStatus;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return BlockService
     */
    protected function getBlockService()
    {
        return $this->createService('Content:BlockService');
    }

    /**
     * @return NavigationService
     */
    protected function getNavigationService()
    {
        return $this->createService('Content:NavigationService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return StatisticsService
     */
    protected function getStatisticsService()
    {
        return $this->createService('System:StatisticsService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
