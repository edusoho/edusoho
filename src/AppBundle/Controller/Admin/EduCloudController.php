<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Exception\FileToolkitException;
use Biz\System\SettingException;
use Imagine\Image\Box;
use Imagine\Gd\Imagine;
use Biz\Util\EdusohoLiveClient;
use AppBundle\Common\FileToolkit;
use Biz\CloudPlatform\KeyApplier;
use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\IMAPIFactory;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\Service\AppService;
use Symfony\Component\HttpFoundation\Request;
use Biz\CloudPlatform\Client\AbstractCloudAPI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class EduCloudController extends BaseController
{
    //概览页，产品简介页
    public function myCloudAction(Request $request)
    {
        // @apitodo 需改成leaf
        try {
            $api = CloudAPIFactory::create('root');
            $info = $api->get('/me');
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/cloud-error.html.twig', array());
        }

        if (isset($info['accessCloud']) && 0 != $info['accessCloud']) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }

        if (!isset($info['accessCloud']) || $this->getWebExtension()->isTrial() || 0 == $info['accessCloud']) {
            $trialHtml = $this->getCloudCenterExperiencePage();

            return $this->render('admin/edu-cloud/cloud.html.twig', array(
                'content' => $trialHtml['content'],
            ));
        }

        $unTrial = file_get_contents('http://open.edusoho.com/api/v1/block/cloud_guide');
        $unTrialHtml = json_decode($unTrial, true);

        return $this->render('admin/edu-cloud/cloud.html.twig', array(
            'content' => $unTrialHtml['content'],
        ));
    }

    //概览页，服务概况页
    // refactor
    public function myCloudOverviewAction(Request $request)
    {
        try {
            $api = CloudAPIFactory::create('root');
            $isBinded = $this->getAppService()->getBinded();
            $overview = $api->get("/cloud/{$api->getAccessKey()}/overview");
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/cloud-error.html.twig', array());
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

        return $this->render('admin/edu-cloud/overview/index.html.twig', array(
            'isBinded' => $isBinded,
            'overview' => $overview,
            'paidService' => isset($paidService) ? $paidService : false,
            'unPaidService' => isset($unPaidService) ? $unPaidService : false,
        ));
    }

    public function attachmentAction(Request $request)
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
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/video-error.html.twig', array());
        }

        return $this->render('admin/edu-cloud/cloud-attachment.html.twig', array(
            'attachment' => $attachment,
            'info' => $info,
        ));
    }

    //云视频概览页
    public function videoOverviewAction(Request $request)
    {
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

    public function showRenewVideoAction(Request $request)
    {
        $renewVideo = $request->query->get('renewVideo');

        return $this->render('admin/edu-cloud/video/video-renew-modal.html.twig', array(
            'renewVideo' => $renewVideo,
        ));
    }

    public function videoSwitchAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();
            $storageSetting = $this->getSettingService()->get('storage', array());
            $storageSetting = array_merge($storageSetting, $set);
            $this->getSettingService()->set('storage', $storageSetting);

            return $this->redirect($this->generateUrl('admin_cloud_video_overview'));
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

    public function videoSettingAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin/edu-cloud/video/trial.html.twig', array());
        }

        if (!($this->isVisibleCloud())) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }
        $storageSetting = $this->getSettingService()->get('storage', array());

        if ((isset($storageSetting['upload_mode']) && 'local' == $storageSetting['upload_mode']) || !isset($storageSetting['upload_mode'])) {
            return $this->redirect($this->generateUrl('admin_cloud_video_overview'));
        }

        $storageSetting = $this->getSettingService()->get('storage', array());
        $default = array(
            'upload_mode' => 'local',
            'support_mobile' => 0,
            'video_h5_enable' => 1,
            'enable_playback_rates' => 0,
            'video_quality' => 'high',
            'video_audio_quality' => 'high',
            'video_watermark' => 0,
            'video_watermark_image' => '',
            'video_embed_watermark_image' => '',
            'video_watermark_position' => 'topright',
            'video_fingerprint' => 0,
            'video_fingerprint_time' => 0.5,
            'video_header' => null,
            'video_auto_play' => 'true',
        );

        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();
            $storageSetting = array_merge($default, $storageSetting, $set);
            if (!empty($set['isDeleteMP4'])) {
                $this->deleteCloudMP4Files();
                $storageSetting['delete_mp4_status'] = 'waiting';
            }
            $this->getSettingService()->set('storage', $storageSetting);
            $this->setFlashMessage('success', 'site.save.success');

            return $this->createJsonResponse(true);
        } else {
            $storageSetting = array_merge($default, $storageSetting);
            $this->getSettingService()->set('storage', $storageSetting);
        }

        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/storage/overview');
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/video-error.html.twig', array());
        }

        try {
            $headLeader = $this->getUploadFileService()->getFileByTargetType('headLeader');
        } catch (\RuntimeException $e) {
            $headLeader = null;
        }

        return $this->render('admin/edu-cloud/video/setting.html.twig', array(
            'storageSetting' => $storageSetting,
            'headLeader' => $headLeader,
            'video' => $overview['video'],
        ));
    }

    public function deleteVideoAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $this->deleteCloudMP4Files();

            $setting = $this->getSettingService()->get('storage', array());
            $setting['delete_mp4_status'] = 'waiting';
            $this->getSettingService()->set('storage', $setting);

            return $this->createJsonResponse(true);
        }

        $hasMp4Video = $this->getCloudFileService()->hasMp4Video();

        if (!$hasMp4Video) {
            return $this->render('admin/edu-cloud/video/video-delete-success-modal.html.twig');
        }

        return $this->render('admin/edu-cloud/video/video-delete-confirm-modal.html.twig');
    }

    public function videoControlAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();
            $storageSetting = $this->getSettingService()->get('storage', array());
            $storageSetting = array_merge($storageSetting, $set);
            $this->getSettingService()->set('storage', $storageSetting);

            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
    }

    public function videoWatermarkUploadAction(Request $request)
    {
        $file = $request->files->get('watermark');

        if (!FileToolkit::isImageFile($file)) {
            $this->createNewException(FileToolkitException::NOT_IMAGE());
        }

        $filename = 'watermark_'.time().'.'.$file->getClientOriginalExtension();

        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/system";
        $file = $file->move($directory, $filename);
        $path = "system/{$filename}";

        $response = array(
            'path' => $path,
            'url' => $this->get('web.twig.extension')->getFileUrl($path),
        );

        return new Response(json_encode($response));
    }

    public function videoEmbedWatermarkUploadAction(Request $request)
    {
        $file = $request->files->get('watermark');

        if (!FileToolkit::isImageFile($file)) {
            $this->createNewException(FileToolkitException::NOT_IMAGE());
        }

        $filename = 'watermarkembed_'.time().'.'.$file->getClientOriginalExtension();

        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/system";
        $file = $file->move($directory, $filename);
        $path = "system/{$filename}";
        $originFileInfo = getimagesize($file);
        $filePath = $this->container->getParameter('topxia.upload.public_directory').'/'.$path;
        $imagine = new Imagine();
        $rawImage = $imagine->open($filePath);

        $pathinfo = pathinfo($filePath);
        $specification['240'] = 20;
        $specification['360'] = 30;
        $specification['480'] = 40;
        $specification['720'] = 60;
        $specification['1080'] = 90;

        foreach ($specification as $key => $value) {
            $width = ($originFileInfo[0] * $value / $originFileInfo[1]);
            $specialImage = $rawImage->copy();
            $specialImage->resize(new Box($width, $value));
            $filePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}-{$key}.{$pathinfo['extension']}";
            $specialImage->save($filePath);
        }

        $response = array(
            'path' => $path,
            'url' => $this->get('web.twig.extension')->getFileUrl($path),
        );

        return new Response(json_encode($response));
    }

    public function videoWatermarkRemoveAction(Request $request)
    {
        return $this->createJsonResponse(true);
    }

    public function smsStatusAction(Request $request)
    {
        $dataUserPosted = $request->request->all();
        $settings = $this->getSettingService()->get('cloud_sms', array());
        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/sms/overview');
            $cloudInfo = $api->get('/me');
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/sms-error.html.twig', array());
        }

        if (isset($overview['isBuy'])) {
            $this->setFlashMessage('danger', 'site.illegal.request');
        }
        $smsStatus = $this->handleUserSmsSetting($dataUserPosted);

        if (empty($cloudInfo['accessCloud'])) {
            return $this->createMessageResponse('info', '对不起，请先接入教育云！', '', 3,
                $this->generateUrl('admin_my_cloud_overview'));
        }

        //启动
        if (isset($dataUserPosted['sms-open'])) {
            $smsStatus = array_merge($smsStatus, $settings);
            $smsStatus['sms_enabled'] = 1;
        }

        $status = $api->get('/me/sms_account');

        if (isset($dataUserPosted['sms-close'])) {
            $smsStatus = array_merge($smsStatus, $settings);
            $smsStatus['sms_enabled'] = 0;
        }

        $smsStatus['status'] = isset($status['status']) ? $status['status'] : 'error';

        $this->getSettingService()->set('cloud_sms', $smsStatus);

        return $this->redirect($this->generateUrl('admin_edu_cloud_sms'));
    }

    //云短信概览页
    public function smsOverviewAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin/edu-cloud/sms/trial.html.twig');
        }

        if (!($this->isVisibleCloud())) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }

        $settings = $this->getSettingService()->get('storage', array());
        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            $this->setFlashMessage('warning', 'admin.cloud.license.has_no_license');

            return $this->redirect($this->generateUrl('admin_setting_cloud_key_update'));
        }

        $cloudSmsSettings = $this->getSettingService()->get('cloud_sms', array());
        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/sms/overview');
            $smsInfo = $api->get('/me/sms_account');
            $this->checkSmsSign($smsInfo);
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/sms-error.html.twig', array());
        }
        $isSmsWithoutEnable = $this->isSmsWithoutEnable($overview, $cloudSmsSettings);
        if ($isSmsWithoutEnable) {
            $overview['isBuy'] = isset($overview['isBuy']) ? $overview['isBuy'] : true;

            return $this->render('admin/edu-cloud/sms/without-enable.html.twig', array(
                'overview' => $overview,
                'cloudSmsSettings' => $cloudSmsSettings,
            ));
        }
        $chartData = $this->dealChartData($overview['data']);

        return $this->render('admin/edu-cloud/sms/overview.html.twig', array(
            'account' => $overview['account'],
            'chartData' => $chartData,
            'smsInfo' => $smsInfo,
        ));
    }

    //云短信设置
    public function smsSettingAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin/edu-cloud/sms/trial.html.twig');
        }

        if (!($this->isVisibleCloud())) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }

        $cloudSmsSettings = $this->getSettingService()->get('cloud_sms', array());
        if ((isset($cloudSmsSettings['sms_enabled']) && 0 == $cloudSmsSettings['sms_enabled']) || !isset($cloudSmsSettings['sms_enabled'])) {
            return $this->redirect($this->generateUrl('admin_edu_cloud_sms'));
        }

        try {
            $api = CloudAPIFactory::create('root');

            if ($request->isMethod('POST')) {
                $this->handleSmsSetting($request, $api);
                $this->setFlashMessage('success', 'site.save.success');
            }
            $smsInfo = $api->get('/me/sms_account');
            $this->checkSmsSign($smsInfo);
            $isBinded = $this->getAppService()->getBinded();

            return $this->render('admin/edu-cloud/sms/setting.html.twig', array(
                'isBinded' => $isBinded,
                'smsInfo' => $smsInfo,
            ));
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/sms-error.html.twig', array());
        }
    }

    protected function checkSmsSign($smsInfo)
    {
        if (empty($smsInfo)) {
            $smsSignUrl = $this->generateUrl('admin_cloud_sms_sign');
            $this->setFlashMessage('danger',
                "尚未开通云短信,不能发送短信, <a href='{$smsSignUrl}' class='plm' target='_blank'>去设置</a>");
        } else {
            if (empty($smsInfo['name']) && empty($smsInfo['isExistSmsSign'])) {
                $smsSignUrl = $this->generateUrl('admin_cloud_sms_sign');
                $this->setFlashMessage('danger',
                    "尚未设置短信签名,不能发送短信, <a href='{$smsSignUrl}' class='plm' target='_blank'>去设置</a>");
            }
            if (empty($smsInfo['name']) && !empty($smsInfo['isExistSmsSign']) && null == $smsInfo['usedSmsSign']) {
                $this->setFlashMessage('danger', 'admin.cloud.sms.signature_in_reviewing');
            }
        }
    }

    //原云邮件设置页
    public function emailAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin/edu-cloud/email.html.twig');
        }

        $settings = $this->getSettingService()->get('storage', array());

        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            $this->setFlashMessage('warning', 'admin.cloud.license.has_no_license');

            return $this->redirect($this->generateUrl('admin_setting_cloud_key_update'));
        }

        try {
            $api = CloudAPIFactory::create('root');
            $info = $api->get('/me');
            $status = $api->get('/me/email_account');
            $emailStatus = $this->handleEmailSetting($request);
            $overview = $api->get("/user/center/{$api->getAccessKey()}/overview");
            $emailInfo = $emailInfo = isset($overview['service']['email']) ? $overview['service']['email'] : null;

            return $this->render('admin/edu-cloud/email/overview.html.twig', array(
                'locked' => isset($info['locked']) ? $info['locked'] : 0,
                'enabled' => isset($info['enabled']) ? $info['enabled'] : 1,
                'email_enable' => isset($status['status']) ? $status['status'] : 'enable',
                'accessCloud' => $this->isAccessEduCloud(),
                'emailStatus' => $emailStatus,
                'emailInfo' => $emailInfo,
            ));
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/email-error.html.twig', array());
        }
    }

    public function emailOverviewAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin/edu-cloud/email/trial.html.twig');
        }

        if (!($this->isVisibleCloud())) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }

        $settings = $this->getSettingService()->get('storage', array());
        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            $this->setFlashMessage('warning', 'admin.cloud.license.has_no_license');

            return $this->redirect($this->generateUrl('admin_setting_cloud_key_update'));
        }

        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/email/overview');
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/email-error.html.twig', array());
        }
        $emailSettings = $this->getSettingService()->get('cloud_email_crm', array());
        $isEmailWithoutEnable = $this->isEmailWithoutEnable($overview, $emailSettings);
        if ($isEmailWithoutEnable) {
            $overview['isBuy'] = isset($overview['isBuy']) ? false : true;

            return $this->render('admin/edu-cloud/email/without-enable.html.twig', array(
                'overview' => $overview,
            ));
        }
        $chartData = $this->dealChartData($overview['data']);

        return $this->render('admin/edu-cloud/email/overview.html.twig', array(
            'account' => $overview['account'],
            'chartData' => $chartData,
        ));
    }

    private function isEmailWithoutEnable($overview, $emailSettings)
    {
        $isEmailWithoutEnable = (isset($emailSettings['status']) && 'disable' == $emailSettings['status']) || !isset($emailSettings['status']) || (isset($overview['isBuy']) && false == $overview['isBuy']);

        return $isEmailWithoutEnable;
    }

    //云邮件设置页
    public function emailSettingAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin/edu-cloud/email/trial.html.twig');
        }

        if (!$this->isVisibleCloud()) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }

        $emailSettings = $this->getSettingService()->get('cloud_email_crm', array());
        if (!isset($emailSettings['status']) || (isset($emailSettings['status']) && 'disable' == $emailSettings['status'])) {
            return $this->redirect($this->generateUrl('admin_edu_cloud_email'));
        }

        $settings = $this->getSettingService()->get('storage', array());

        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            $this->setFlashMessage('warning', 'admin.cloud.license.has_no_license');

            return $this->redirect($this->generateUrl('admin_setting_cloud_key_update'));
        }
        try {
            $api = CloudAPIFactory::create('root');
            $account = $api->get('/me/email_account');

            return $this->render('admin/edu-cloud/email/setting.html.twig', array(
                'account' => $account,
            ));
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/email-error.html.twig', array());
        }
    }

    public function emailSwitchAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            try {
                $api = CloudAPIFactory::create('root');
                $overview = $api->get('/me/email/overview');
            } catch (\RuntimeException $e) {
                return $this->render('admin/edu-cloud/email-error.html.twig', array());
            }

            if (isset($overview['isBuy'])) {
                $this->setFlashMessage('danger', 'site.illegal.request');
            }
            $status = $request->request->all();
            if (isset($status['email-open'])) {
                $emailStatus['status'] = 'enable';
                $this->getSettingService()->set('cloud_email_crm', $emailStatus);

                $mailer = $this->getSettingService()->get('mailer');
                $mailer['enabled'] = 0;
                $this->getSettingService()->set('mailer', $mailer);
            }

            if (isset($status['email-close'])) {
                $emailStatus['status'] = 'disable';
                $this->getSettingService()->set('cloud_email_crm', $emailStatus);
            }

            return $this->redirect($this->generateUrl('admin_edu_cloud_email'));
        }
    }

    public function applyForSmsAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $result = null;
            $dataUserPosted = $request->request->all();

            if (
                isset($dataUserPosted['name'])
                && ($this->calStrlen($dataUserPosted['name']) >= 2)
                && ($this->calStrlen($dataUserPosted['name']) <= 16)
            ) {
                $api = CloudAPIFactory::create('root');
                $result = $api->post("/sms/{$api->getAccessKey()}/apply", array('name' => $dataUserPosted['name']));

                if (isset($result['status']) && ('ok' == $result['status'])) {
                    $this->setCloudSmsKey('sms_school_candidate_name', $dataUserPosted['name']);
                    $this->setCloudSmsKey('show_message', 'on');

                    return $this->createJsonResponse(array('ACK' => 'ok'));
                }
            }

            return $this->createJsonResponse(array(
                'ACK' => 'failed',
                'message' => $result['error'].'|'.($this->calStrlen($dataUserPosted['name'])),
            ));
        }

        return $this->render('admin/edu-cloud/apply-sms-form.html.twig', array());
    }

    public function smsNoMessageAction(Request $request)
    {
        $this->setCloudSmsKey('show_message', 'off');

        return $this->redirect($this->generateUrl('admin_edu_cloud_sms', array()));
    }

    //授权页
    public function keyAction(Request $request)
    {
        $settings = $this->getSettingService()->get('storage', array());

        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            return $this->redirect($this->generateUrl('admin_setting_cloud_key_update'));
        }

        $info = array();
        try {
            $api = CloudAPIFactory::create('root');
            $info = $api->get('/me');
        } catch (\RuntimeException $e) {
            $info['error'] = 'error';
        }

        return $this->render('admin/edu-cloud/key.html.twig', array(
            'info' => $info,
        ));
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

        return $this->render('admin/edu-cloud/key-license-info.html.twig', array(
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

    public function keyUpdateAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->redirect($this->generateUrl('admin_setting_cloud_key'));
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

            return $this->redirect($this->generateUrl('admin_setting_cloud_key'));
        }

        render:

        return $this->render('admin/edu-cloud/key-update.html.twig', array());
    }

    public function searchSettingAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin/edu-cloud/search/trial.html.twig');
        }

        if (!($this->isVisibleCloud())) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }

        $cloudSearchSettting = $this->getSettingService()->get('cloud_search', array());
        if (!$cloudSearchSettting['search_enabled']) {
            return $this->redirect($this->generateUrl('admin_edu_cloud_search'));
        }

        $cloudSearchSetting = $this->getSettingService()->get('cloud_search', array());
        $searchInitStatus = $this->checkCloudSearchStatus($cloudSearchSetting);

        return $this->render('admin/edu-cloud/search/setting.html.twig', array(
            'searchInitStatus' => $searchInitStatus,
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

    public function searchOverviewAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin/edu-cloud/search/trial.html.twig');
        }

        if (!$this->isVisibleCloud()) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }

        $cloud_search_setting = $this->getSettingService()->get('cloud_search', array());
        try {
            $api = CloudAPIFactory::create('root');

            $userOverview = $api->get("/users/{$api->getAccessKey()}/overview");
            $searchOverview = $api->get('/me/search/overview');
            $data = $this->initCloudSearch($api, $cloud_search_setting);
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/search/without-enable.html.twig', array(
                'data' => array('status' => 'unlink'),
            ));
        }

        //判断云搜索状态
        if (empty($userOverview['user']['licenseDomains'])) {
            $data['status'] = 'unbinded';
        } else {
            $currentHost = $request->server->get('HTTP_HOST');
            if (!in_array($currentHost, explode(';', $userOverview['user']['licenseDomains']), true)) {
                $data['status'] = 'binded_error';
            }
        }
        if (!isset($searchOverview['isBuy']) && 1 == $data['search_enabled'] && ('ok' == $data['status'] || 'waiting' == $data['status'])) {
            $chartData = $this->dealChartData($searchOverview['data']);

            return $this->render('admin/edu-cloud/search/overview.html.twig', array(
                'searchOverview' => $searchOverview,
                'chartData' => $chartData,
            ));
        } else {
            return $this->render('admin/edu-cloud/search/without-enable.html.twig', array(
                'data' => $data,
            ));
        }
    }

    public function searchReapplyAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $callbackRouteUrl = $this->generateUrl('edu_cloud_search_callback');
            $this->getSearchService()->applySearchAccount($callbackRouteUrl);
            $this->getSearchService()->refactorAllDocuments();

            return $this->redirect($this->generateUrl('admin_edu_cloud_search'));
        }

        return $this->render('admin/edu-cloud/cloud-search-reapply-modal.html.twig');
    }

    public function searchClauseAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $callbackRouteUrl = $this->generateUrl('edu_cloud_search_callback');
            $this->getSearchService()->applySearchAccount($callbackRouteUrl);

            return $this->redirect($this->generateUrl('admin_edu_cloud_search'));
        }

        return $this->render('admin/edu-cloud/cloud-search-clause-modal.html.twig');
    }

    public function searchOpenAction()
    {
        $cloud_search_setting = $this->getSettingService()->get('cloud_search', array());
        if ('ok' == $cloud_search_setting['status'] || 'waiting' == $cloud_search_setting['status']) {
            $cloud_search_setting['search_enabled'] = 1;
            $this->getSettingService()->set('cloud_search', $cloud_search_setting);
        }

        return $this->redirect($this->generateUrl('admin_edu_cloud_search'));
    }

    public function searchCloseAction()
    {
        $cloud_search_setting = $this->getSettingService()->get('cloud_search', array());
        $cloud_search_setting['search_enabled'] = 0;
        $this->getSettingService()->set('cloud_search', $cloud_search_setting);

        return $this->redirect($this->generateUrl('admin_edu_cloud_search'));
    }

    public function setSearchResultTypeAction(Request $request)
    {
        $newSetting = $request->query->all();

        $cloud_search_setting = $this->getSettingService()->get('cloud_search');

        $differentSetting = array_diff_assoc($cloud_search_setting['type'], $newSetting);
        foreach ($cloud_search_setting['type'] as $key => &$type) {
            $type = 1;
            if ('course' !== (string) $key && array_key_exists($key, $differentSetting)) {
                $type = 0;
            }
        }

        $this->getSettingService()->set('cloud_search', $cloud_search_setting);

        return $this->redirect($this->generateUrl('admin_edu_cloud_setting_search'));
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

    public function appImAction(Request $request)
    {
        $appImSetting = $this->getSettingService()->get('app_im', array());
        if (!$appImSetting) {
            $appImSetting = array('enabled' => 0, 'convNo' => '');
            $this->getSettingService()->set('app_im', $appImSetting);
        }

        $data = array('status' => 'success');

        try {
            $api = CloudAPIFactory::create('root');

            $overview = $api->get("/users/{$api->getAccessKey()}/overview");
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/video-error.html.twig', array());
        }

        //是否接入教育云
        if (empty($overview['user']['level']) || (!(isset($overview['service']['storage'])) && !(isset($overview['service']['live'])) && !(isset($overview['service']['sms'])))) {
            $data['status'] = 'unconnect';
        } elseif (empty($overview['user']['licenseDomains'])) {
            $data['status'] = 'unbinded';
        } else {
            $currentHost = $request->server->get('HTTP_HOST');
            if (!in_array($currentHost, explode(';', $overview['user']['licenseDomains']))) {
                $data['status'] = 'binded_error';
            }
        }

        return $this->render('admin/edu-cloud/app-im-setting.html.twig', array(
            'data' => $data,
        ));
    }

    public function appImUpdateStatusAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $appImSetting = $this->getSettingService()->get('app_im', array());
            $user = $this->getUser();

            //去云平台判断im帐号是否存在
            $api = IMAPIFactory::create();
            $imAccount = $api->get('/me/account');

            if (isset($imAccount['error']) || empty($imAccount['account'])) {
                $imAccount = $api->post('/accounts');
            }

            $status = $request->request->get('status', 0);
            $imStatus = $status ? 'enable' : 'disable';

            //更改云IM帐号状态
            $api->post('/me/account', array('status' => $imStatus));

            $appImSetting['enabled'] = $status;

            //创建全站会话
            if ($status && empty($appImSetting['convNo'])) {
                $conversation = $this->getConversationService()->createConversation('全站会话', 'global', 0, array($user));
                if ($conversation) {
                    $appImSetting['convNo'] = $conversation['no'];
                }
            }

            $this->getSettingService()->set('app_im', $appImSetting);

            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
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

    protected function dateFormat($time)
    {
        return strtotime(substr($time, 0, 4).'-'.substr($time, 4, 2));
    }

    protected function monthDays($time)
    {
        return date('t', strtotime("{$time}-1"));
    }

    private function isSmsWithoutEnable($overview, $cloudSmsSettings)
    {
        $isSmsWithoutEnable = (isset($overview['isBuy']) && false == $overview['isBuy']) || (isset($cloudSmsSettings['sms_enabled']) && 0 == $cloudSmsSettings['sms_enabled']) || !isset($cloudSmsSettings['sms_enabled']);

        return $isSmsWithoutEnable;
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
     * 处理支付成功回执的总开关.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
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

    protected function handleEmailSetting(Request $request)
    {
        $dataUserPosted = $request->request->all();
        list($emailStatus, $sign) = $this->getSign($dataUserPosted);

        $emailStatus = array_merge($emailStatus, $sign);

        if ('error' != $emailStatus['status'] && !empty($dataUserPosted)) {
            $this->getSettingService()->set('cloud_email_crm', $emailStatus);
        }

        $emailStatus = $this->getSettingService()->get('cloud_email_crm', array());

        return $emailStatus;
    }

    protected function getSign($operation)
    {
        $api = CloudAPIFactory::create('root');
        $settings = $this->getSettingService()->get('cloud_email_crm', array());
        $result = array();
        $sign = array();
        $emailStatus = array();

        if (isset($operation['email-open'])) {
            $status = $api->get('/me/email_account');

            if (isset($status['error']) && 101 == $status['error']['code']) {
                $site = $this->getSettingService()->get('site', array());
                $result = $api->post('/email_accounts',
                    array('sender' => isset($site['name']) ? $site['name'] : '我的网校'));

                if (isset($result['status']) && 'enable' == $result['status']) {
                    $emailStatus['status'] = 'enable';
                    $emailStatus = array_merge($settings, $emailStatus);
                    $sign = array('sign' => $result['nickname']);
                }
            } else {
                $emailStatus['status'] = 'enable';
                $emailStatus = array_merge($settings, $emailStatus);
                $sign = array('sign' => $settings['sign']);
            }

            $result = $api->get('/me/email_account');
            $this->setFlashMessage('success', 'site.save.success');
            $mailer = $this->getSettingService()->get('mailer', array());

            if (isset($result['status']) && 'enable' == $result['status'] && '1' == $mailer['enabled']) {
                $default = array(
                    'enabled' => 0,
                    'host' => '',
                    'port' => '',
                    'username' => '',
                    'password' => '',
                    'from' => '',
                    'name' => '',
                );
                $mailer = array_merge($default, $mailer);
                $mailer['enabled'] = 0;
                $this->getSettingService()->set('mailer', $mailer);
                $mailerWithoutPassword = $mailer;
                $mailerWithoutPassword['password'] = '******';
                $this->getLogService()->info('system', 'update_settings', '开启云邮件关闭第三方邮件服务器设置', $mailerWithoutPassword);
            }
        }

        if (isset($operation['sign'])) {
            if (!empty($operation['sign'])) {
                $params = array(
                    'sender' => $operation['sign'],
                );
                $result = $api->post('/me/email_account', $params);

                if (isset($result['nickname'])) {
                    $this->setFlashMessage('success', 'site.save.success');
                    $emailStatus['status'] = $settings['status'];
                    $sign = array('sign' => $result['nickname']);
                } else {
                    $this->setFlashMessage('danger', 'site.save.fail');
                }
            } else {
                $emailStatus['status'] = $settings['status'];
                $sign = array('sign' => $settings['sign']);
            }
        }

        if (isset($operation['email-close'])) {
            $emailStatus['status'] = 'disable';
            $emailStatus = array_merge($settings, $emailStatus);
            $this->setFlashMessage('success', 'site.save.success');
        }

        if (empty($operation)) {
            $result = $api->get('/me/email_account');

            if (isset($result['nickname'])) {
                $emailStatus['status'] = $result['status'];
                $sign = array('sign' => $result['nickname']);
            } else {
                $emailStatus['status'] = 'disable';
                $sign = isset($settings['sign']) ? array('sign' => $settings['sign']) : array('sign' => '');
            }
        }

        if (isset($result['error'])) {
            $emailStatus['status'] = 'error';
            $emailStatus['msg'] = $result['error'];
        }

        return array($emailStatus, $sign);
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

    protected function generateVideoChartData($info)
    {
        if (empty($info)) {
            $info = array();
        }

        $chartInfo = array();

        foreach ($info as $key => $value) {
            $chartInfo[] = '{"date":"'.$value['date'].'","spacecount":"'.$value['space'].'","transfercount":"'.$value['transfer'].'"}';
        }

        return '['.implode(',', $chartInfo).']';
    }

    protected function generateChartData($info)
    {
        if (empty($info)) {
            $info = array();
        }

        $chartInfo = array();

        foreach ($info as $key => $value) {
            $chartInfo[] = '{"date":"'.$key.'","count":'.$value.'}';
        }

        return '['.implode(',', $chartInfo).']';
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

    protected function getCloudCenterExperiencePage()
    {
        $trial = file_get_contents('http://open.edusoho.com/api/v1/block/experience');
        $trialHtml = json_decode($trial, true);

        return $trialHtml;
    }

    protected function isAccessEduCloud()
    {
        try {
            $api = CloudAPIFactory::create('root');
            $info = $api->get('/me');

            return isset($info['accessCloud']) ? $info['accessCloud'] : 0;
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/cloud-error.html.twig', array());
        }
    }

    protected function isSearchInited($api, $data)
    {
        if (!$data) {
            $data = array(
                'search_enabled' => 0,
                'status' => 'closed', //'closed':未开启；'waiting':'索引中';'ok':'索引完成'
            );
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
        $this->getSettingService()->set('cloud_search', $data);

        return $data;
    }

    protected function getImUsedInfo()
    {
        $api = IMAPIFactory::create();
        $endTime = strtotime(date('Y-m-d', strtotime('-1 day')).' 23:59:59');
        $startTime = strtotime(date('Y-m-d', strtotime('-6 days', $endTime)).'00:00:00');

        try {
            $imUsedInfo = $api->get('/me/receive_count_period', array(
                'startTime' => $startTime,
                'endTime' => $endTime,
            ));

            if (isset($imUsedInfo['error'])) {
                return array();
            }
        } catch (\RuntimeException $e) {
            return array();
        }

        $chartInfo = array();
        foreach ($imUsedInfo as $value) {
            $chartInfo[] = array('date' => $value['sendTime'], 'count' => $value['nums']);
        }

        return $chartInfo;
    }

    protected function createGlobalImConversation()
    {
        $user = $this->getUser();
        $message = array(
            'name' => '站点会话',
            'clients' => array(
                array(
                    'clientId' => $user['id'],
                    'clientName' => $user['nickname'],
                ),
            ),
        );

        $result = IMAPIFactory::create()->post('/me/conversation', $message);

        if (isset($result['error'])) {
            return '';
        }

        return $result['no'];
    }

    private function dealChartData($data)
    {
        $chartData['unit'] = $data['unit'];
        if (empty($data['items'])) {
            for ($i = 7; $i > 0; --$i) {
                $chartData['date'][] = date('Y-m-d', strtotime('-'.$i.'days'));
                $chartData['count'][] = 0;
            }

            return $chartData;
        }

        foreach ($data['items'] as $item) {
            $chartData['date'][] = $item['date'];
            $chartData['count'][] = $item['count'];
        }

        return $chartData;
    }

    protected function getSearchService()
    {
        return $this->createService('Search:SearchService');
    }

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

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getSignEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }

    protected function getConversationService()
    {
        return $this->createService('IM:ConversationService');
    }

    // 添加云直播
    public function liveOverviewAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin/edu-cloud/live/trial.html.twig');
        }

        if (!($this->isVisibleCloud())) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }

        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/live/overview');
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/live-error.html.twig', array());
        }
        $liveCourseSetting = $this->getSettingService()->get('live-course', array());
        $liveEnabled = isset($liveCourseSetting['live_course_enabled']) ? $liveCourseSetting['live_course_enabled'] : 0;
        $isLiveWithoutEnable = $this->isLiveWithoutEnable($overview, $liveEnabled);
        if ($isLiveWithoutEnable) {
            $overview['isBuy'] = isset($overview['isBuy']) ? $overview['isBuy'] : true;

            return $this->render('admin/edu-cloud/live/without-enable.html.twig', array(
                'overview' => $overview,
            ));
        }
        $chartData = $this->dealChartData($overview['data']);

        return $this->render('admin/edu-cloud/live/overview.html.twig', array(
            'account' => $overview['account'],
            'chartData' => $chartData,
        ));
    }

    private function isLiveWithoutEnable($overview, $liveEnabled)
    {
        $isLiveWithoutEnable = (isset($overview['isBuy']) && false == $overview['isBuy']) || 0 == $liveEnabled || !isset($liveEnabled);

        return $isLiveWithoutEnable;
    }

    private function isVisibleCloud()
    {
        return $this->getEduCloudService()->isVisibleCloud();
    }

    public function liveSettingAction(Request $request)
    {
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

    public function logoCropAction(Request $request, $type)
    {
        if (!in_array($type, array('web', 'app'))) {
            return $this->createMessageResponse('error', '参数不正确');
        }

        if ('POST' === $request->getMethod()) {
            $options = $request->request->all();

            $image = $options['images'][0];
            $file = $this->getFileService()->getFile($image['id']);

            $liveSetting = $this->getSettingService()->get('live-course', array());
            $url = $this->get('web.twig.extension')->getFurl($file['uri']);

            $oldFileId = empty($liveSetting["{$type}LogoFileId"]) ? '' : $liveSetting["{$type}LogoFileId"];
            if ($oldFileId) {
                $this->getFileService()->deleteFile($oldFileId);
            }

            $liveSetting["{$type}LogoFileId"] = $file['id'];
            $liveSetting["{$type}LogoPath"] = $url;

            $this->getSettingService()->set('live-course', $liveSetting);

            return $this->createJsonResponse(array('fileId' => $file['id'], 'url' => $url, 'type' => $type));
        }

        $fileId = $request->getSession()->get('fileId');

        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 100, 100);

        return $this->render('admin/edu-cloud/live/logo-crop-modal.html.twig', array(
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
            'type' => $type,
        ));
    }

    public function consultSettingAction(Request $request)
    {
        if (!$this->isVisibleCloud()) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }

        try {
            $account = $this->getConsultService()->getAccount();
            $jsResource = $this->getConsultService()->getJsResource();
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/consult/consult-error.html.twig', array());
        }

        $cloudConsult = $this->getConsultService()->buildCloudConsult($account, $jsResource);

        if (isset($cloudConsult['error'])) {
            $this->setFlashMessage('danger', $cloudConsult['error']);
        }

        unset($cloudConsult['error']);
        if (0 == $cloudConsult['cloud_consult_is_buy']) {
            $this->getSettingService()->set('cloud_consult', $cloudConsult);

            return $this->renderConsultWithoutEnable($cloudConsult);
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $cloudConsult['cloud_consult_setting_enabled'] = $data['cloud_consult_setting_enabled'];

            $this->getSettingService()->set('cloud_consult', $cloudConsult);
            $this->setFlashMessage('success', 'site.save.success');
        }

        if (0 == $cloudConsult['cloud_consult_setting_enabled']) {
            return $this->renderConsultWithoutEnable($cloudConsult);
        }

        return $this->render('admin/edu-cloud/consult/setting.html.twig', array(
            'cloud_consult' => $cloudConsult,
        ));
    }

    public function getAdAction()
    {
        $api = CloudAPIFactory::create('root');
        $result = $api->get('/edusoho-ad');

        return $this->createJsonResponse($result);
    }

    private function renderConsultWithoutEnable($cloudConsult)
    {
        return $this->render('admin/edu-cloud/consult/without-enable.html.twig', array(
            'cloud_consult' => $cloudConsult,
        ));
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

    protected function deleteCloudMP4Files()
    {
        $user = $this->getUser();

        $callback = $this->get('request')->getSchemeAndHttpHost().$this->generateUrl('callback', array('type' => 'cloudFile', 'ac' => 'files.notify'));

        $this->getCloudFileService()->deleteCloudMP4Files($user['id'], $callback);

        return true;
    }

    protected function getConsultService()
    {
        return $this->createService('EduCloud:MicroyanConsultService');
    }

    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    protected function getCloudFileService()
    {
        return $this->createService('CloudFile:CloudFileService');
    }
}
