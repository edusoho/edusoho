<?php

namespace AppBundle\Controller\AdminV2;

use AppBundle\Common\ChangelogToolkit;
use AppBundle\Common\CurlToolkit;
use Biz\Common\CommonException;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ThreadService;
use Biz\QuickEntrance\Service\QuickEntranceService;
use Biz\System\Service\SettingService;
use Biz\System\Service\StatisticsService;
use Biz\User\Service\NotificationService;
use Codeages\Biz\Order\Service\OrderService;
use QiQiuYun\SDK\Service\PlatformNewsService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class DefaultController extends BaseController
{
    public function indexAction(Request $request)
    {
        $weekAndMonthDate = array('weekDate' => date('Y-m-d', time() - 6 * 24 * 60 * 60), 'monthDate' => date('Y-m-d', time() - 29 * 24 * 60 * 60));

        return $this->render('admin-v2/default/index.html.twig', array(
            'dates' => $weekAndMonthDate,
        ));
    }

    public function changelogAction(Request $request)
    {
        $rootDir = $this->getParameter('kernel.root_dir');
        $changelogPath = $rootDir.'/../CHANGELOG';
        $changelog = explode(PHP_EOL.PHP_EOL, file_get_contents($changelogPath));
        $currentChangeLog = ChangelogToolkit::parseSingleChangelog($changelog[0]);

        return $this->render('admin-v2/default/changelog.html.twig', array(
            'currentChangelog' => $currentChangeLog,
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

    public function switchOldVersionAction(Request $request)
    {
        $setting = $this->getSettingService()->get('backstage', array('is_v2' => 0));

        if (!empty($setting) && !$setting['is_v2']) {
            $this->createNewException(CommonException::SWITCH_OLD_VERSION_ERROR());
        }

        $roles = $this->getCurrentUser()->getRoles();
        if (0 == count(array_intersect($roles, array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) || empty($setting['allow_show_switch_btn'])) {
            $this->createNewException(CommonException::SWITCH_OLD_VERSION_PERMISSION_ERROR());
        }

        if ('POST' == $request->getMethod()) {
            $setting['is_v2'] = 0;
            $this->getSettingService()->set('backstage', $setting);

            return $this->createJsonResponse(array('status' => 'success', 'url' => $this->generateUrl('admin')));
        }

        return $this->render('admin-v2/default/switch-old-version-modal.html.twig', array());
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

    public function applicationIntroAction(Request $request)
    {
        $result = $this->getPlatformNewsSdkService()->getApplications();

        return $this->render('admin-v2/default/application-intro.html.twig', array(
            'applicationData' => empty($result['details']) ? array() : $result['details'],
            'returnUrl' => empty($result['returnUrl']) ? '' : $result['returnUrl'],
        ));
    }

    public function businessAdviceAction()
    {
        $advice = array();
        if (!$this->isWithoutNetwork()) {
            try {
                $advice = $this->getPlatformNewsSdkService()->getAdvice();
            } catch (\Exception $e) {
                $advice = array();
            }
        }

        return $this->render('admin-v2/default/business-advice.html.twig', array(
            'advice' => $advice,
        ));
    }

    public function getAnnouncementFromPlatformAction(Request $request)
    {
        $result = $this->getPlatformNewsSdkService()->getAnnouncements();

        return $this->render('admin-v2/default/announcement.html.twig', array(
            'announcement' => empty($result['details']) ? array() : array_pop($result['details']),
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

    public function quickEntranceAction(Request $request)
    {
        $userQuickEntrances = $this->getQuickEntranceService()->getEntrancesByUserId($this->getCurrentUser()->getId());

        if ($request->isMethod('POST')) {
            $fields = $request->request->all();
            $userQuickEntrances = $this->getQuickEntranceService()->updateUserEntrances($this->getCurrentUser()->getId(), $fields);
        }

        $allQuickEntrances = $this->getQuickEntranceService()->getAllEntrances($this->getCurrentUser()->getId());

        return $this->render('admin-v2/default/quick-entrance/index.html.twig', array(
            'allQuickEntrances' => $allQuickEntrances,
            'userQuickEntrances' => $userQuickEntrances,
        ));
    }

    public function qrCodeAction(Request $request)
    {
        if ($this->isWithoutNetwork()) {
            $qrCode = array();
        } else {
            try {
                $qrCode = $this->getPlatformNewsSdkService()->getQrCode();
                $qrCode = empty($qrCode['details']) ? array() : array_pop($qrCode['details']);
            } catch (\Exception $e) {
                $qrCode = array();
            }
        }

        return $this->render('admin-v2/default/qr-code.html.twig', array(
            'qrCode' => $qrCode,
        ));
    }

    protected function isWithoutNetwork()
    {
        $developer = $this->getSettingService()->get('developer');

        return empty($developer['without_network']) ? false : (bool) $developer['without_network'];
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
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
     * @return QuickEntranceService
     */
    protected function getQuickEntranceService()
    {
        return $this->createService('QuickEntrance:QuickEntranceService');
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

    /**
     * @return PlatformNewsService
     */
    protected function getPlatformNewsSdkService()
    {
        $biz = $this->getBiz();

        return $biz['qiQiuYunSdk.platformNews'];
    }
}
