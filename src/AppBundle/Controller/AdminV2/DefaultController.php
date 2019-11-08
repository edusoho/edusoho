<?php

namespace AppBundle\Controller\AdminV2;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\CurlToolkit;
use AppBundle\System;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\Service\AppService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ThreadService;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use Biz\WeChat\Service\WeChatAppService;
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

        $upgradeAppCount = count($apps);

        $indexApps = ArrayToolkit::index($apps, 'code');
        $mainAppUpgrade = empty($indexApps['MAIN']) ? array() : $indexApps['MAIN'];

        if ($mainAppUpgrade) {
            $upgradeAppCount = $upgradeAppCount - 1;
        }

        return $this->render('admin-v2/default/school-info.html.twig', array(
            'version' => System::VERSION,
            'mainAppUpgrade' => $mainAppUpgrade,
            'upgradeAppCount' => $upgradeAppCount,
            'disabledCloudServiceCount' => $this->getDisabledCloudServiceCount(),
            'wechatAppStatus' => array('installed'=>1),//$this->getWeChatAppService()->getWeChatAppStatus(),
            'schoolLevel' => $this->getSchoolLevelKey(),
        ));
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
}
