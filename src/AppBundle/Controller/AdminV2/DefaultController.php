<?php

namespace AppBundle\Controller\AdminV2;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\CurlToolkit;
use AppBundle\Common\FileToolkit;
use AppBundle\System;
use AppBundle\Common\ChangelogToolkit;
use Biz\Common\CommonException;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\Service\AppService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ThreadService;
use Biz\QuickEntrance\Service\QuickEntranceService;
use Biz\System\Service\SettingService;
use Biz\System\Service\StatisticsService;
use Biz\User\Service\NotificationService;
use Biz\WeChat\Service\WeChatAppService;
use QiQiuYun\SDK\Service\WeChatService;
use Codeages\Biz\Order\Service\OrderService;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
use QiQiuYun\SDK\Service\PlatformNewsService;

class DefaultController extends BaseController
{
    public function indexAction(Request $request)
    {
        $weekAndMonthDate = array('weekDate' => date('Y-m-d', time() - 6 * 24 * 60 * 60), 'monthDate' => date('Y-m-d', time() - 29 * 24 * 60 * 60));

        $userQuickEntrances = $this->getQuickEntranceService()->getEntrancesByUserId($this->getCurrentUser()->getId());

        return $this->render('admin-v2/default/index.html.twig', array(
            'dates' => $weekAndMonthDate,
            'entrances' => $userQuickEntrances,
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

    public function infoAction(Request $request)
    {
        $apps = $this->getAppService()->checkAppUpgrades();
        $indexApps = ArrayToolkit::index($apps, 'code');
        $mainAppUpgrade = empty($indexApps['MAIN']) ? array() : $indexApps['MAIN'];
        $upgradeAppCount = empty($mainAppUpgrade) ? count($apps) : count($apps) - 1;

        return $this->render('admin-v2/default/school-info.html.twig', array(
            'version' => System::VERSION,
            'mainAppUpgrade' => $mainAppUpgrade,
            'upgradeAppCount' => $upgradeAppCount,
            'disabledCloudServiceCount' => $this->getDisabledCloudServiceCount(),
            'wechatAppStatus' => $this->getWeChatAppService()->getWeChatAppStatus(),
            'schoolLevel' => $this->getSchoolLevelKey(),
            'miniProgramCodeImg' => $this->getMiniProgramCodeImg(),
        ));
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

    protected function getMiniProgramCodeImg()
    {
        if ($this->isMiniProgramCodeImgNeedGenerate()) {
            $res = $this->getSDKWeChatService()->getMiniProgramCode('backgroundHome', array('width' => 280));

            $tmpPath = tempnam(sys_get_temp_dir(), 'mini_program');
            file_put_contents($tmpPath, base64_decode($res['content']));
            $miniProgramCodeImg = new File($tmpPath);
            $directory = "{$this->getParameter('topxia.upload.public_directory')}/system";
            $filename = FileToolkit::generateFilename('png');
            $miniProgramCodeImg = $miniProgramCodeImg->move($directory, $filename);
            $this->deleteExpiredMiniProgramCodeImg();
            $this->getSettingService()->set('mini_program', array(
                'get_code_time' => time(),
                'img_path' => $miniProgramCodeImg->getRealPath(),
                'img_url' => $this->get('web.twig.extension')->getFileUrl("system/{$filename}"),
            ));
        }

        return $this->getSettingService()->get('mini_program', array());
    }

    private function isMiniProgramCodeImgNeedGenerate()
    {
        $miniProgram = $this->getSettingService()->get('mini_program', array());
        if (empty($miniProgram['get_code_time']) || $miniProgram['get_code_time'] < time() - 2 * 3600) {
            return true;
        }
        if (empty($miniProgram['img_path']) || !file_exists($miniProgram['img_path'])) {
            return true;
        }

        return false;
    }

    private function deleteExpiredMiniProgramCodeImg()
    {
        $miniProgram = $this->getSettingService()->get('mini_program', array());
        if (!empty($miniProgram['img_path']) && file_exists($miniProgram['img_path'])) {
            unlink($miniProgram['img_path']);
        }
    }

    public function quickEntranceAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $fields = $request->request->all();
            $quickEntrances = $this->getQuickEntranceService()->updateUserEntrances($this->getCurrentUser()->getId(), $fields);

            return $this->render('admin-v2/default/quick-entrance/index.html.twig', array('entrances' => $quickEntrances));
        }

        $quickEntrances = $this->getQuickEntranceService()->getAllEntrances($this->getCurrentUser()->getId());

        return $this->render('admin-v2/default/quick-entrance/modal.html.twig', array('entranceData' => $quickEntrances));
    }

    protected function getDisabledCloudServiceCount()
    {
        $disabledCloudServiceCount = 0;

        $settingKeys = array(
            'course.live_course_enabled' => '',
            'cloud_sms.sms_enabled' => '',
            'cloud_search.search_enabled' => '',
            'cloud_consult.cloud_consult_setting_enabled' => 0,
            'storage.upload_mode' => 'cloud',
        );

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

    protected function getSchoolLevelKey()
    {
        $settings = $this->getSettingService()->get('storage', array());
        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            return 'none';
        }

        $info = array();
        try {
            $info = CloudAPIFactory::create('root')->get('/me');
        } catch (\RuntimeException $e) {
            $info['error'] = 'error';
        }

        if (empty($info['userLevel'])) {
            return 'none';
        }
        if (in_array($info['userLevel'], array('none', 'license', 'custom', 'saas'))) {
            return $info['userLevel'];
        }

        return 'none';
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
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return WeChatAppService
     */
    protected function getWeChatAppService()
    {
        return $this->createService('WeChat:WeChatAppService');
    }

    /**
     * @return WeChatService
     */
    protected function getSDKWeChatService()
    {
        $biz = $this->getBiz();

        return $biz['qiQiuYunSdk.wechat'];
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
