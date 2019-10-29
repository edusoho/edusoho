<?php

namespace AppBundle\Controller\AdminV2\CloudCenter;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\Client\AbstractCloudAPI;
use Biz\CloudPlatform\KeyApplier;
use Biz\CloudPlatform\Service\EduCloudService;
use Biz\System\Service\SettingService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\System\SettingException;
use Biz\Util\EdusohoLiveClient;
use Biz\CloudPlatform\Service\AppService;
use Symfony\Component\HttpFoundation\Request;

class EduCloudController extends BaseController
{
    //概览页，服务概况页
    // refactor
    public function myCloudOverviewAction(Request $request)
    {
        try {
            $api = CloudAPIFactory::create('root');
            $isBinded = $this->getAppService()->getBinded();
            $overview = $api->get("/cloud/{$api->getAccessKey()}/overview");
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/cloud-error.html.twig', array());
        }
        if (!isset($overview['error'])) {
            $paidService = array();
            $unPaidService = array();
            $this->getSettingService()->set('cloud_status', array(
                'enabled' => $overview['enabled'],
                'locked' => $overview['locked'],
                'accessCloud' => $overview['accessCloud'],
            ));

            foreach ($overview['services'] as $key => $value) {
                if (true == $value) {
                    $paidService[] = $key;
                } else {
                    $unPaidService[] = $key;
                }
            }

            foreach ($unPaidService as $key => $value) {
                if ('search' == $value) {
                    unset($unPaidService[$key]);
                }
            }
        }

        return $this->render('admin-v2/cloud-center/edu-cloud/overview/index.html.twig', array(
            'isBinded' => $isBinded,
            'overview' => $overview,
            'paidService' => isset($paidService) ? $paidService : false,
            'unPaidService' => isset($unPaidService) ? $unPaidService : false,
        ));
    }

    public function liveSettingAction(Request $request)
    {
        //直播业务有用到只迁移了action需要后面修改云模块的修改
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin/edu-cloud/live/trial.html.twig');
        }

        if (!$this->isVisibleCloud()) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }

        $liveCourseSetting = $this->getSettingService()->get('live-course', array());
        $client = new EdusohoLiveClient();
        $capacity = $client->getCapacity();

        if ($request->isMethod('POST')) {
            try {
                $api = CloudAPIFactory::create('root');
                $overview = $api->get('/me/live/overview');
            } catch (\RuntimeException $e) {
                return $this->render('admin/edu-cloud/live-error.html.twig', array());
            }

            if (isset($overview['isBuy'])) {
                $this->setFlashMessage('danger', 'site.illegal.request');
            }

            $live = $request->request->all();
            $liveCourseSetting = array_merge($liveCourseSetting, $live);
            $liveCourseSetting['live_student_capacity'] = empty($capacity['capacity']) ? 0 : $capacity['capacity'];

            $courseSetting = $this->getSettingService()->get('course', array());
            $setting = array_merge($courseSetting, $liveCourseSetting);
            $this->getSettingService()->set('live-course', $liveCourseSetting);
            $this->getSettingService()->set('course', $setting);

            $this->setCloudLiveLogo($capacity['provider'], $client);

            $redirectUrl = 'talkFun' == $capacity['provider'] ? 'admin_setting_cloud_edulive' : 'admin_cloud_edulive_overview';

            return $this->redirect($this->generateUrl($redirectUrl));
        }

        if (empty($liveCourseSetting['live_course_enabled'])) {
            return $this->redirect($this->generateUrl('admin_cloud_edulive_overview'));
        }

        $liveEnabled = $liveCourseSetting['live_course_enabled'];
        if (null === $liveEnabled || 0 === $liveEnabled) {
            return $this->redirect($this->generateUrl('admin_cloud_edulive_overview'));
        }
        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/live/overview');
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/live-error.html.twig', array());
        }

        return $this->render('admin/edu-cloud/live/setting.html.twig', array(
            'account' => $overview['account'],
            'liveCourseSetting' => $liveCourseSetting,
            'capacity' => $capacity,
        ));
    }

    //云视频概览页
    public function videoOverviewAction(Request $request)
    {
        //附件业务有用到只迁移了action需要后面修改云模块的修改
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin/edu-cloud/video/trial.html.twig', array());
        }

        if (!($this->isVisibleCloud())) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }

        $storageSetting = $this->getSettingService()->get('storage', array());
        //云端视频判断
        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/storage/overview');
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/video-error.html.twig', array());
        }
        if ((isset($storageSetting['upload_mode']) && 'local' == $storageSetting['upload_mode']) || !isset($storageSetting['upload_mode'])) {
            return $this->render('admin/edu-cloud/video/without-enable.html.twig');
        }

        $overview['video']['isBuy'] = isset($overview['video']['isBuy']) ? false : true;
        $overview['yearPackage']['isBuy'] = isset($overview['yearPackage']['isBuy']) ? false : true;

        $spaceItems = $this->dealItems($overview['video']['spaceItems']);
        $flowItems = $this->dealItems($overview['video']['flowItems']);

        return $this->render('admin/edu-cloud/video/overview.html.twig', array(
            'video' => $overview['video'],
            'space' => isset($overview['space']) ? $overview['space'] : null,
            'flow' => isset($overview['flow']) ? $overview['flow'] : null,
            'yearPackage' => $overview['yearPackage'],
            'spaceItems' => $spaceItems,
            'flowItems' => $flowItems,
        ));
    }

    public function attachmentSettingAction(Request $request)
    {
        $attachment = $this->getSettingService()->get('cloud_attachment', array());
        $defaultData = array('article' => 0, 'course' => 0, 'classroom' => 0, 'group' => 0, 'question' => 0);
        $default = array_merge($defaultData, array('enable' => 0, 'fileSize' => 500));
        $attachment = array_merge($default, $attachment);

        if ('POST' == $request->getMethod()) {
            $attachment = $request->request->all();
            $attachment = array_merge($default, $attachment);
            $this->getSettingService()->set('cloud_attachment', $attachment);
            $this->setFlashMessage('success', 'site.save.success');
        }
        //云端视频判断
        try {
            $api = CloudAPIFactory::create('root');
            $info = $api->get('/me');
            if (empty($info['accessKey'])) {
                return $this->render('admin-v2/cloud-center/edu-cloud/not-access.html.twig', array('menu' => 'admin_v2_cloud_attachment_setting'));
            }
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/video-error.html.twig', array());
        }

        return $this->render('admin-v2/cloud-center/edu-cloud/cloud-attachment.html.twig', array(
            'attachment' => $attachment,
            'info' => $info,
        ));
    }

    //授权页
    public function keyAction(Request $request)
    {
        $settings = $this->getSettingService()->get('storage', array());

        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            return $this->redirect($this->generateUrl('admin_v2_setting_cloud_key_update'));
        }

        $info = array();
        try {
            $api = CloudAPIFactory::create('root');
            $info = $api->get('/me');
        } catch (\RuntimeException $e) {
            $info['error'] = 'error';
        }

        return $this->render('admin-v2/cloud-center/edu-cloud/key.html.twig', array(
            'info' => $info,
        ));
    }

    public function keyUpdateAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->redirect($this->generateUrl('admin_v2_setting_cloud_key'));
        }

        $settings = $this->getSettingService()->get('storage', array());

        if ('POST' == $request->getMethod()) {
            $options = $request->request->all();

            $api = CloudAPIFactory::create('root');
            $api->setKey($options['accessKey'], $options['secretKey']);

            $result = $api->post(sprintf('/keys/%s/verification', $options['accessKey']));

            if (isset($result['error'])) {
                $this->setFlashMessage('danger', 'admin.cloud.license.incorrect');
                goto render;
            }

            $user = $api->get('/me');

            if ('opensource' != $user['edition']) {
                $this->setFlashMessage('danger', 'admin.cloud.license.edition_mismatching');
                goto render;
            }

            $settings['cloud_access_key'] = $options['accessKey'];
            $settings['cloud_secret_key'] = $options['secretKey'];
            $settings['cloud_key_applied'] = 1;

            $this->getSettingService()->set('storage', $settings);

            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect($this->generateUrl('admin_v2_setting_cloud_key'));
        }

        render:

        return $this->render('admin-v2/cloud-center/edu-cloud/key-update.html.twig', array());
    }

    public function keyApplyAction(Request $request)
    {
        $applier = new KeyApplier();
        $keys = $applier->applyKey($this->getUser());

        if (empty($keys['accessKey']) || empty($keys['secretKey'])) {
            return $this->createJsonResponse(array('error' => 'Key生成失败，请检查服务器网络后，重试！'));
        }

        $settings = $this->getSettingService()->get('storage', array());

        $settings['cloud_access_key'] = $keys['accessKey'];
        $settings['cloud_secret_key'] = $keys['secretKey'];
        $settings['cloud_key_applied'] = 1;

        $this->getSettingService()->set('storage', $settings);

        return $this->createJsonResponse(array('status' => 'ok'));
    }

    public function keyInfoAction(Request $request)
    {
        $api = CloudAPIFactory::create('root');
        $info = $api->get('/me');

        if (!empty($info['accessKey'])) {
            $settings = $this->getSettingService()->get('storage', array());

            if (empty($settings['cloud_key_applied'])) {
                $settings['cloud_key_applied'] = 1;
                $this->getSettingService()->set('storage', $settings);
            }

            if ($info['copyright']) {
                $copyright = $this->getSettingService()->get('copyright', array());
                $copyright['owned'] = 1;
                $copyright['thirdCopyright'] = $info['thirdCopyright'];
                $copyright['licenseDomains'] = $info['licenseDomains'];
                $this->getSettingService()->set('copyright', $copyright);
            } else {
                $this->getSettingService()->delete('copyright');
            }
        } else {
            $settings = $this->getSettingService()->get('storage', array());
            $settings['cloud_key_applied'] = 0;
            $this->getSettingService()->set('storage', $settings);
        }

        $currentHost = $request->server->get('HTTP_HOST');

        if (isset($info['licenseDomains'])) {
            $info['licenseDomainCount'] = count(explode(';', $info['licenseDomains']));
        }

        return $this->render('admin-v2/cloud-center/edu-cloud/key-license-info.html.twig', array(
            'info' => $info,
            'currentHost' => $currentHost,
            'isLocalAddress' => $this->isLocalAddress($currentHost),
        ));
    }

    public function keyBindAction(Request $request)
    {
        $api = CloudAPIFactory::create('root');
        $currentHost = $request->server->get('HTTP_HOST');
        $result = $api->post('/me/license-domain', array('domain' => $currentHost));

        if (!empty($result['licenseDomains'])) {
            $this->setFlashMessage('success', 'admin.cloud.license.activate_success');
        } else {
            $this->setFlashMessage('danger', 'admin.cloud.license.activate_fail');
        }

        return $this->createJsonResponse($result);
    }

    public function keyCopyrightAction(Request $request)
    {
        $api = CloudAPIFactory::create('leaf');
        $info = $api->get('/me');

        if (empty($info['copyright'])) {
            $this->createNewException(SettingException::NO_COPYRIGHT());
        }

        $name = $request->request->get('name');

        $this->getSettingService()->set('copyright', array(
            'owned' => 1,
            'name' => $request->request->get('name', ''),
            'thirdCopyright' => isset($info['thirdCopyright']) ? $info['thirdCopyright'] : 0,
            'licenseDomains' => isset($info['licenseDomains']) ? $info['licenseDomains'] : '',
        ));

        return $this->createJsonResponse(array('status' => 'ok'));
    }

    protected function isLocalAddress($address)
    {
        if (in_array($address, array('localhost', '127.0.0.1'))) {
            return true;
        }

        if (0 === strpos($address, '192.168.')) {
            return true;
        }

        if (0 === strpos($address, '10.')) {
            return true;
        }

        return false;
    }

    private function renderConsultWithoutEnable($cloudConsult)
    {
        return $this->render('admin-v2/cloud-center/edu-cloud/consult/without-enable.html.twig', array(
            'cloud_consult' => $cloudConsult,
        ));
    }

    protected function checkCloudSearchStatus($cloudSearchSetting)
    {
        if ('waiting' == $cloudSearchSetting['status']) {
            $api = CloudAPIFactory::create('root');
            $search_account = $api->get('/me/search_account');

            if ('yes' == $search_account['isInit']) {
                $searchInitStatus = 'init';
            } else {
                $searchInitStatus = 'notInit';
            }
        }

        if ('ok' == $cloudSearchSetting['status']) {
            $searchInitStatus = 'init';
        }

        return isset($searchInitStatus) ? $searchInitStatus : '';
    }

    protected function initCloudSearch(AbstractCloudAPI $api, $data)
    {
        if (!$data) {
            $data = array(
                'search_enabled' => 0,
                'status' => 'closed', //'closed':未开启；'waiting':'索引中';'ok':'索引完成'
            );
        }

        if (empty($data['status'])) {
            $data['status'] = 'closed';
        }

        if ('waiting' == $data['status']) {
            $search_account = $api->get('/me/search_account');

            if ('yes' == $search_account['isInit']) {
                $data = array(
                    'search_enabled' => $data['search_enabled'],
                    'status' => 'ok',
                );
            }
        }
        if (empty($data['type'])) {
            $data['type'] = array(
                'course' => 1,
                'classroom' => 1,
                'teacher' => 1,
                'thread' => 1,
                'article' => 1,
            );
        }
        $this->getSettingService()->set('cloud_search', $data);

        return $data;
    }

    private function isEmailWithoutEnable($overview, $emailSettings)
    {
        $isEmailWithoutEnable = (isset($emailSettings['status']) && 'disable' == $emailSettings['status']) || !isset($emailSettings['status']) || (isset($overview['isBuy']) && false == $overview['isBuy']);

        return $isEmailWithoutEnable;
    }

    protected function calStrlen($str)
    {
        return (strlen($str) + mb_strlen($str, 'UTF8')) / 2;
    }

    protected function setCloudSmsKey($key, $val)
    {
        $setting = $this->getSettingService()->get('cloud_sms', array());
        $setting[$key] = $val;
        $this->getSettingService()->set('cloud_sms', $setting);
    }

    private function isLiveWithoutEnable($overview, $liveEnabled)
    {
        $isLiveWithoutEnable = (isset($overview['isBuy']) && false == $overview['isBuy']) || 0 == $liveEnabled || !isset($liveEnabled);

        return $isLiveWithoutEnable;
    }

    protected function deleteCloudMP4Files()
    {
        $user = $this->getUser();

        $callback = $this->get('request')->getSchemeAndHttpHost().$this->generateUrl('callback', array('type' => 'cloudFile', 'ac' => 'files.notify'));

        $this->getCloudFileService()->deleteCloudMP4Files($user['id'], $callback);

        return true;
    }

    protected function handleSmsSetting(Request $request, $api)
    {
        $dataUserPosted = $request->request->all();
        $settings = $this->getSettingService()->get('cloud_sms', array());

        //如果要更改短信策略则同步到数据库
        if (isset($dataUserPosted['strategy_overwrite'])) {
            $smsStatus = array_merge($settings, $dataUserPosted);
            $smsStatus = $this->updateSmstrategy($smsStatus, $dataUserPosted);
            $this->getSettingService()->set('cloud_sms', $smsStatus);
        }

        if (empty($settings)) {
            $smsStatus = $this->handleUserSmsSetting($dataUserPosted);
            $status = $api->get('/me/sms_account');
            $smsStatus['status'] = isset($status['status']) ? $status['status'] : 'error';

            $this->getSettingService()->set('cloud_sms', $smsStatus);
        }
    }

    /**
     * 默认的短信策略.
     *
     * @var [type]
     */
    private function handleUserSmsSetting($dataUserPosted)
    {
        $defaultSetting = array(
            'sms_enabled' => '0',
            'sms_registration' => 'off',
            'sms_forget_password' => 'on',
            'sms_user_pay' => 'on',
            'sms_forget_pay_password' => 'on',
            'sms_bind' => 'on',
            'sms_login' => 'on',
            'sms_classroom_publish' => 'off',
            'sms_course_publish' => 'off',
            'sms_normal_lesson_publish' => 'off',
            'sms_live_lesson_publish' => 'off',
            'sms_live_play_one_day' => 'off',
            'sms_live_play_one_hour' => 'off',
            'sms_homework_check' => 'off',
            'sms_testpaper_check' => 'off',
            'sms_order_pay_success' => 'off',
            'sms_course_buy_notify' => 'off',
            'sms_classroom_buy_notify' => 'off',
            'sms_vip_buy_notify' => 'off',
            'sms_coin_buy_notify' => 'off',
        );

        $dataUserPosted = ArrayToolkit::filter($dataUserPosted, $defaultSetting);

        return array_merge($defaultSetting, $dataUserPosted);
    }

    private function updateSmstrategy($smsStatus, $dataUserPosted)
    {
        if ('on' == $dataUserPosted['sms_order_pay_success']) {
            $smsStatus['sms_course_buy_notify'] = 'on';
            $smsStatus['sms_classroom_buy_notify'] = 'on';
            $smsStatus['sms_vip_buy_notify'] = 'on';
            $smsStatus['sms_coin_buy_notify'] = 'on';
        } else {
            $smsStatus['sms_course_buy_notify'] = 'off';
            $smsStatus['sms_classroom_buy_notify'] = 'off';
            $smsStatus['sms_vip_buy_notify'] = 'off';
            $smsStatus['sms_coin_buy_notify'] = 'off';
        }

        return $smsStatus;
    }

    protected function checkSmsSign($smsInfo)
    {
        if (empty($smsInfo)) {
            $smsSignUrl = $this->generateUrl('admin_v2_cloud_sms_sign');
            $this->setFlashMessage('danger',
                "尚未开通云短信,不能发送短信, <a href='{$smsSignUrl}' class='plm' target='_blank'>去设置</a>");
        } else {
            if (empty($smsInfo['name']) && empty($smsInfo['isExistSmsSign'])) {
                $smsSignUrl = $this->generateUrl('admin_v2_cloud_sms_sign');
                $this->setFlashMessage('danger',
                    "尚未设置短信签名,不能发送短信, <a href='{$smsSignUrl}' class='plm' target='_blank'>去设置</a>");
            }
            if (empty($smsInfo['name']) && !empty($smsInfo['isExistSmsSign']) && null == $smsInfo['usedSmsSign']) {
                $this->setFlashMessage('danger', 'admin.cloud.sms.signature_in_reviewing');
            }
        }
    }

    private function dealItems($data)
    {
        if (empty($data)) {
            for ($i = 7; $i > 0; --$i) {
                $items['date'][] = date('Y-m-d', strtotime('-'.$i.'days'));
                $items['amount'][] = 0;
            }

            return $items;
        }

        foreach ($data as $value) {
            $items['date'][] = $value['date'];
            $items['amount'][] = $value['amount'];
        }

        return $items;
    }

    protected function setCloudLiveLogo($provider, $client)
    {
        $setting = $this->getSettingService()->get('live-course', array());

        $isSetLogo = !empty($setting['webLogoPath']) || !empty($setting['appLogoPath']) || !empty($setting['logoUrl']);

        if ('talkFun' == $provider && $isSetLogo) {
            $logoData = array(
                'logoPcUrl' => empty($setting['webLogoPath']) ? '' : $setting['webLogoPath'],
                'logoClientUrl' => empty($setting['appLogoPath']) ? '' : $setting['appLogoPath'],
                'logoGotoUrl' => empty($setting['logoUrl']) ? 'http://www.talk-fun.com' : $setting['logoUrl'],
            );
            $result = $client->setLiveLogo($logoData);

            if (isset($result['error'])) {
                return $this->createMessageResponse('error', '设置直播logo出错');
            }
        }

        return true;
    }

    private function isVisibleCloud()
    {
        return $this->getEduCloudService()->isVisibleCloud();
    }

    /**
     * @return EduCloudService
     */
    protected function getEduCloudService()
    {
        return $this->createService('CloudPlatform:EduCloudService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
