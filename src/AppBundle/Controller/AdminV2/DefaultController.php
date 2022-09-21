<?php

namespace AppBundle\Controller\AdminV2;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ChangelogToolkit;
use AppBundle\Common\FileToolkit;
use AppBundle\System;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\Service\AppService;
use Biz\Common\CommonException;
use Biz\Content\Service\BlockService;
use Biz\Content\Service\NavigationService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ThreadService;
use Biz\QuickEntrance\Service\QuickEntranceService;
use Biz\System\Service\SettingService;
use Biz\System\Service\StatisticsService;
use Biz\User\Service\NotificationService;
use Biz\WeChat\Service\WeChatAppService;
use Codeages\Biz\Order\Service\OrderService;
use QiQiuYun\SDK\Service\PlatformNewsService;
use QiQiuYun\SDK\Service\WeChatService;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Topxia\Service\Common\ServiceKernel;

class DefaultController extends BaseController
{
    const ADMIN_V2_VERSION = '8.5.0';

    public function indexAction(Request $request)
    {
        return $this->render('admin-v2/default/index.html.twig', [
            'isNewcomerTaskAllDone' => $this->isNewcomerTaskAllDone(),
            'isSetConsult' => $this->validateConsultSetting(),
//            'showChatGroupQrCode' => 'none' == $this->getSchoolLevelKey(),
        ]);
    }

    public function newcomerAction(Request $request)
    {
        return $this->render('admin-v2/default/newcomer-task.html.twig', [
            'newcomerTasks' => $this->getNewcomerTasksWithStatus(),
        ]);
    }

    public function changelogAction(Request $request)
    {
        $rootDir = $this->getParameter('kernel.root_dir');
        $changelogPath = $rootDir.'/../CHANGELOG';
        $changelog = explode(PHP_EOL.PHP_EOL, file_get_contents($changelogPath));
        $currentChangeLog = ChangelogToolkit::parseSingleChangelog($changelog[0]);

        return $this->render('admin-v2/default/changelog.html.twig', [
            'currentChangelog' => $currentChangeLog,
        ]);
    }

    public function statisticsDailyAction(Request $request)
    {
        $todayTimeStart = strtotime(date('Y-m-d', time()));
        $todayTimeEnd = strtotime(date('Y-m-d', time() + 24 * 3600));

        $loginCount = $this->getStatisticsService()->countLogin(time() - 15 * 60);
        $registerNum = $this->getUserService()->countUsers(['startTime' => $todayTimeStart, 'endTime' => $todayTimeEnd]);

        $conditions = [
            'pay_time_GT' => $todayTimeStart,
            'pay_time_LT' => $todayTimeEnd,
            'statuses' => ['paid', 'success', 'finished', 'refunded'],
        ];

        $newOrderCount = $this->getOrderService()->countOrders($conditions);
        $conditions['pay_amount_GT'] = 0;

        $newPaidOrderCount = $this->getOrderService()->countOrders($conditions);

        return $this->render('admin-v2/default/daily-statistics.html.twig', [
            'loginCount' => $loginCount,
            'registerNum' => $registerNum,
            'newOrderCount' => $newOrderCount,
            'newPaidOrderCount' => $newPaidOrderCount,
        ]);
    }

    public function infoAction(Request $request)
    {
        $apps = $this->getAppService()->checkAppUpgrades();
        $indexApps = ArrayToolkit::index($apps, 'code');
        $mainAppUpgrade = empty($indexApps['MAIN']) ? [] : $indexApps['MAIN'];
        $upgradeAppCount = empty($mainAppUpgrade) ? count($apps) : count($apps) - 1;

        return $this->render('admin-v2/default/school-info.html.twig', [
            'version' => System::VERSION,
            'mainAppUpgrade' => $mainAppUpgrade,
            'upgradeAppCount' => $upgradeAppCount,
            'disabledCloudServiceCount' => $this->getDisabledCloudServiceCount(),
            'wechatAppStatus' => $this->getWeChatAppService()->getWeChatAppStatus(),
            'schoolLevel' => $this->getSchoolLevelKey(),
            'miniProgramCodeImg' => $this->getMiniProgramCodeImg(),
        ]);
    }

    public function switchOldVersionAction(Request $request)
    {
        $setting = $this->getSettingService()->get('backstage', ['is_v2' => 0]);

        if (!empty($setting) && !$setting['is_v2']) {
            $this->createNewException(CommonException::SWITCH_OLD_VERSION_ERROR());
        }

        $roles = $this->getCurrentUser()->getRoles();
        if (0 == count(array_intersect($roles, ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])) || empty($setting['allow_show_switch_btn'])) {
            $this->createNewException(CommonException::SWITCH_OLD_VERSION_PERMISSION_ERROR());
        }

        if ('POST' == $request->getMethod()) {
            $setting['is_v2'] = 0;
            $this->getSettingService()->set('backstage', $setting);
            $this->pushEventTracking('switchToAdmin');

            return $this->createJsonResponse(['status' => 'success', 'url' => $this->generateUrl('admin')]);
        }

        return $this->render('admin-v2/default/switch-old-version-modal.html.twig', []);
    }

    public function validateUpgradeAction(Request $request)
    {
        $canUpgradeApps = $this->getAppService()->checkAppUpgrades();
        $backstageSetting = $this->getSettingService()->get('backstage', []);
        if (isset($backstageSetting['show_plugin_upgrade_notice']) && 0 == $backstageSetting['show_plugin_upgrade_notice']) {
            return $this->render('admin-v2/default/upgrade-notice.html.twig', ['notice' => false]);
        }

        if (count($canUpgradeApps)) {
            $apps = $this->getAppService()->findAppsByTypes(['theme', 'plugin']);
            foreach ($apps as $app) {
                $canSupportAdminV2 = $this->canSupportAdminV2($app['code'], $app['type']);

                if (!$canSupportAdminV2) {
                    return $this->render('admin-v2/default/upgrade-notice.html.twig', ['notice' => true]);
                }
            }
        }

        $backstageSetting['show_plugin_upgrade_notice'] = 0;
        $this->getSettingService()->set('backstage', $backstageSetting);

        return $this->render('admin-v2/default/upgrade-notice.html.twig', ['notice' => false]);
    }

    protected function canSupportAdminV2($appCode, $appType)
    {
        $rootDir = ServiceKernel::instance()->getParameter('kernel.root_dir');
        if ('plugin' == $appType) {
            $jsonFile = "{$rootDir}/../plugins/{$appCode}Plugin/plugin.json";
        }

        if ('theme' == $appType) {
            $jsonFile = "{$rootDir}/../web/themes/{$appCode}/theme.json";
        }

        if (empty($jsonFile)) {
            return false;
        }

        $appDetail = json_decode(file_get_contents($jsonFile), true);
        $supportVersion = substr($appDetail['support_version'], 0, strlen($appDetail['support_version']) - 1);

        return version_compare($supportVersion, self::ADMIN_V2_VERSION, '>=');
    }

    public function validateDomainAction(Request $request)
    {
        $result = $this->domainInspect($request);

        if ('ok' == $result['status']) {
            return $this->render('admin-v2/default/domain.html.twig', ['inspectList' => []]);
        }

        return $this->render('admin-v2/default/domain.html.twig', [
            'inspectList' => ['name' => 'host', 'value' => $result],
        ]);
    }

    public function getCloudNoticesAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            $domain = $this->generateUrl('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $api = CloudAPIFactory::create('root');
            $result = $api->get('/trial/remainDays', ['domain' => $domain]);
        }

        return $this->render('admin-v2/default/cloud-notice.html.twig', [
            'trialTime' => (isset($result)) ? $result : null,
        ]);
    }

    public function applicationIntroAction(Request $request)
    {
        $result = [];
        if ($this->isSdkAvailable()) {
            try {
                $result = $this->getPlatformNewsSdkService()->getApplications();
            } catch (\Exception $e) {
                $result = [];
            }
        }

        return $this->render('admin-v2/default/application-intro.html.twig', [
            'applicationData' => empty($result['details']) ? [] : $result['details'],
            'returnUrl' => empty($result['returnUrl']) ? '' : $result['returnUrl'],
        ]);
    }

    public function businessAdviceAction()
    {
        $advice = [];
        if (!$this->isWithoutNetwork() && $this->isSdkAvailable()) {
            try {
                $advice = $this->getPlatformNewsSdkService()->getAdvice();
            } catch (\Exception $e) {
                $advice = [];
            }
        }

        return $this->render('admin-v2/default/business-advice.html.twig', [
            'advice' => $advice,
        ]);
    }

    public function getAnnouncementFromPlatformAction(Request $request)
    {
        $result = [];
        if ($this->isSdkAvailable()) {
            try {
                $result = $this->getPlatformNewsSdkService()->getAnnouncements();
            } catch (\Exception $e) {
                $result = [];
            }
        }

        return $this->render('admin-v2/default/announcement.html.twig', [
            'announcement' => empty($result['details']) ? [] : array_pop($result['details']),
        ]);
    }

    private function domainInspect($request)
    {
        $currentHost = $request->server->get('HTTP_HOST');
        $siteSetting = $this->getSettingService()->get('site');
        $settingUrl = $this->generateUrl('admin_v2_school_information');
        $filter = ['http://', 'https://'];
        $siteSetting['url'] = rtrim($siteSetting['url']);
        $siteSetting['url'] = rtrim($siteSetting['url'], '/');

        if ($currentHost != str_replace($filter, '', $siteSetting['url'])) {
            return [
                'status' => 'warning',
                'errorMessage' => ServiceKernel::instance()->trans('admin_v2.domain_error_hint'),
                'except' => $siteSetting['url'],
                'actually' => $currentHost,
                'settingUrl' => $settingUrl,
            ];
        }

        return ['status' => 'ok', 'except' => $siteSetting['url'], 'actually' => $currentHost, 'settingUrl' => $settingUrl];
    }

    protected function getMiniProgramCodeImg()
    {
        if ($this->isMiniProgramCodeImgNeedGenerate() && $this->isSdkAvailable()) {
            try {
                $res = $this->getSDKWeChatService()->getMiniProgramCode('backgroundHome', ['width' => 280]);
            } catch (\Exception $e) {
                return $this->getSettingService()->get('mini_program', []);
            }

            $tmpPath = tempnam(sys_get_temp_dir(), 'mini_program');
            file_put_contents($tmpPath, base64_decode($res['content']));
            $miniProgramCodeImg = new File($tmpPath);
            $directory = "{$this->getParameter('topxia.upload.public_directory')}/system";
            $filename = FileToolkit::generateFilename('png');
            $miniProgramCodeImg = $miniProgramCodeImg->move($directory, $filename);
            $this->deleteExpiredMiniProgramCodeImg();
            $this->getSettingService()->set('mini_program', [
                'get_code_time' => time(),
                'img_path' => $miniProgramCodeImg->getRealPath(),
                'img_url' => $this->get('web.twig.extension')->getFileUrl("system/{$filename}"),
            ]);
        }

        return $this->getSettingService()->get('mini_program', []);
    }

    private function isMiniProgramCodeImgNeedGenerate()
    {
        $miniProgram = $this->getSettingService()->get('mini_program', []);
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
        $miniProgram = $this->getSettingService()->get('mini_program', []);
        if (!empty($miniProgram['img_path']) && file_exists($miniProgram['img_path'])) {
            unlink($miniProgram['img_path']);
        }
    }

    protected function isNewcomerTaskAllDone()
    {
        $newcomerTasksConfig = $this->getNewcomerTasksConfig();
        $newcomerTasks = $this->getNewcomerTasksWithStatus();
        $tasksStatus = ArrayToolkit::column($newcomerTasks, 'status');

        //获取的完成数 与 配置的任务数量比较
        if (array_sum($tasksStatus) == count($newcomerTasksConfig)) {
            return true;
        }

        return false;
    }

    protected function getNewcomerTasksWithStatus()
    {
        $newcomerTasks = $this->getNewcomerTasksConfig();
        foreach ($newcomerTasks as $key => $newComerTaskConfig) {
            $biz = $this->getBiz();
            $keyClass = $biz['newcomer.'.$key];
            $newcomerTasks[$key]['status'] = $keyClass->getStatus();
        }

        return $newcomerTasks;
    }

    protected function getNewcomerTasksConfig()
    {
        return $this->container->get('extension.manager')->getNewcomerTasks();
    }

    public function quickEntranceAction(Request $request)
    {
        $userQuickEntrances = $this->getQuickEntranceService()->findEntrancesByUserId($this->getCurrentUser()->getId());

        if ($request->isMethod('POST')) {
            $entrances = $request->request->get('data', []);
            $userQuickEntrances = $this->getQuickEntranceService()->updateUserEntrances($this->getCurrentUser()->getId(), $entrances);
        }

        $allQuickEntrances = $this->getQuickEntranceService()->findAvailableEntrances();
        $selectedEntranceCodes = $this->getQuickEntranceService()->findSelectedEntrancesCodeByUserId($this->getCurrentUser()->getId());

        return $this->render('admin-v2/default/quick-entrance/index.html.twig', [
            'allQuickEntrances' => $allQuickEntrances,
            'userQuickEntrances' => $userQuickEntrances,
            'selectedEntranceCodes' => $selectedEntranceCodes,
        ]);
    }

    public function qrCodeAction(Request $request)
    {
        if ($this->isWithoutNetwork() || !$this->isSdkAvailable()) {
            $qrCode = [];
        } else {
            try {
                $qrCode = $this->getPlatformNewsSdkService()->getQrCode();
                $qrCode = empty($qrCode['details']) ? [] : array_pop($qrCode['details']);
            } catch (\Exception $e) {
                $qrCode = [];
            }
        }

        return $this->render('admin-v2/default/qr-code.html.twig', [
            'qrCode' => $qrCode,
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
            $this->getNotificationService()->notify($receiverId, 'questionRemind', $message);
        }

        return $this->createJsonResponse(['success' => true, 'message' => 'ok']);
    }

    protected function getDisabledCloudServiceCount()
    {
        $disabledCloudServiceCount = 0;

        $settingKeys = [
//            云直播
            'course.live_course_enabled' => '',
//            云短信
            'cloud_sms.sms_enabled' => '',
//            云搜索
            'cloud_search.search_enabled' => '',
//            云问答
            'cloud_consult.cloud_consult_setting_enabled' => 0,
//            云视频、云文档
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

    protected function getSchoolLevelKey()
    {
        $settings = $this->getSettingService()->get('storage', []);
        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            return 'none';
        }

        $info = [];
        try {
            $info = CloudAPIFactory::create('root')->get('/me');
        } catch (\RuntimeException $e) {
            $info['error'] = 'error';
        }

        if (empty($info['userLevel'])) {
            return 'none';
        }
        if (in_array($info['userLevel'], ['none', 'license', 'custom', 'saas'])) {
            return $info['userLevel'];
        }

        return 'none';
    }

    protected function isWithoutNetwork()
    {
        $developer = $this->getSettingService()->get('developer');

        return empty($developer['without_network']) ? false : (bool) $developer['without_network'];
    }

    protected function isSdkAvailable()
    {
        $storage = $this->getSettingService()->get('storage', []);
        if (empty($storage['cloud_access_key']) || empty($storage['cloud_secret_key'])) {
            return false;
        }

        return true;
    }

    protected function validateConsultSetting()
    {
        $isSetConsult = true;
        $user = $this->getCurrentUser();
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && $consult = $this->getSettingService()->get('consult', [])) {
            $phoneNumbers = ArrayToolkit::column($consult['phone'], 'number');
            if ($consult['enabled'] && [''] == array_unique($phoneNumbers)) {
                $isSetConsult = false;
            }
        }

        return $isSetConsult;
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

        return $biz['ESCloudSdk.wechat'];
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
