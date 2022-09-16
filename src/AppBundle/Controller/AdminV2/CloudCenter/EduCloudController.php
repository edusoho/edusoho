<?php

namespace AppBundle\Controller\AdminV2\CloudCenter;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\FileToolkitException;
use AppBundle\Common\FileToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudFile\Service\CloudFileService;
use Biz\CloudPlatform\Client\AbstractCloudAPI;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\IMAPIFactory;
use Biz\CloudPlatform\KeyApplier;
use Biz\CloudPlatform\Service\AppService;
use Biz\CloudPlatform\Service\EduCloudService;
use Biz\Content\Service\FileService;
use Biz\EduCloud\Service\Impl\MicroyanConsultServiceImpl;
use Biz\File\Service\UploadFileService;
use Biz\IM\Service\ConversationService;
use Biz\Search\Service\SearchService;
use Biz\System\Service\SettingService;
use Biz\System\SettingException;
use Biz\User\UserException;
use Biz\Util\EdusohoLiveClient;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EduCloudController extends BaseController
{
    public function myCloudAction(Request $request)
    {
        // @apitodo 需改成leaf
        try {
            $api = CloudAPIFactory::create('root');
            $info = $api->get('/me');
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/cloud-error.html.twig', []);
        }

        if (isset($info['accessCloud']) && 0 != $info['accessCloud']) {
            return $this->redirect($this->generateUrl('admin_v2_my_cloud_overview'));
        }

        if (!isset($info['accessCloud']) || $this->getWebExtension()->isTrial() || 0 == $info['accessCloud']) {
            $trialHtml = $this->getCloudCenterExperiencePage();

            return $this->render('admin/edu-cloud/cloud.html.twig', [
                'content' => $trialHtml['content'],
            ]);
        }

        $unTrial = file_get_contents('http://open.edusoho.com/api/v1/block/cloud_guide');
        $unTrialHtml = json_decode($unTrial, true);

        return $this->render('admin-v2/cloud-center/edu-cloud/cloud.html.twig', [
            'content' => $unTrialHtml['content'],
        ]);
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
            return $this->render('admin-v2/cloud-center/edu-cloud/cloud-error.html.twig', []);
        }

        // 重置 cloud_status cache
        $this->getCacheService()->set('cloud_status', json_encode($overview), time() + 3600);

        if (!isset($overview['error'])) {
            $paidService = [];
            $unPaidService = [];
            $this->getSettingService()->set('cloud_status', [
                'enabled' => $overview['enabled'],
                'locked' => $overview['locked'],
                'accessCloud' => $overview['accessCloud'],
            ]);

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

        return $this->render('admin-v2/cloud-center/edu-cloud/overview/index.html.twig', [
            'isBinded' => $isBinded,
            'overview' => $overview,
            'paidService' => isset($paidService) ? $paidService : false,
            'unPaidService' => isset($unPaidService) ? $unPaidService : false,
        ]);
    }

    public function getAdAction()
    {
        $api = CloudAPIFactory::create('root');
        $result = $api->get('/edusoho-ad', ['adType' => 'newBackground']);

        return $this->createJsonResponse($result);
    }

    //云短信设置
    public function smsSettingAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin-v2/cloud-center/edu-cloud/sms/trial.html.twig');
        }

        if (!($this->isVisibleCloud())) {
            return $this->redirect($this->generateUrl('admin_v2_my_cloud_overview'));
        }

        $cloudSmsSettings = $this->getSettingService()->get('cloud_sms', []);
        if ((isset($cloudSmsSettings['sms_enabled']) && 0 == $cloudSmsSettings['sms_enabled']) || !isset($cloudSmsSettings['sms_enabled'])) {
            return $this->redirect($this->generateUrl('admin_v2_edu_cloud_sms_overview'));
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

            return $this->render('admin-v2/cloud-center/edu-cloud/sms/setting.html.twig', [
                'isBinded' => $isBinded,
                'smsInfo' => $smsInfo,
            ]);
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/sms-error.html.twig', []);
        }
    }

    public function liveOverviewAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin-v2/cloud-center/edu-cloud/live/trial.html.twig');
        }

        if (!($this->isVisibleCloud())) {
            return $this->redirect($this->generateUrl('admin_v2_my_cloud_overview'));
        }

        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/live/overview');
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/live-error.html.twig', []);
        }
        $liveCourseSetting = $this->getSettingService()->get('live-course', []);
        $liveEnabled = isset($liveCourseSetting['live_course_enabled']) ? $liveCourseSetting['live_course_enabled'] : 0;
        $isLiveWithoutEnable = $this->isLiveWithoutEnable($overview, $liveEnabled);
        if ($isLiveWithoutEnable) {
            $overview['isBuy'] = isset($overview['isBuy']) ? $overview['isBuy'] : true;

            return $this->render('admin-v2/cloud-center/edu-cloud/live/without-enable.html.twig', [
                'overview' => $overview,
            ]);
        }
        $chartData = $this->dealChartData($overview['data']);

        return $this->render('admin-v2/cloud-center/edu-cloud/live/overview.html.twig', [
            'account' => $overview['account'],
            'chartData' => $chartData,
        ]);
    }

    public function liveSettingAction(Request $request)
    {
        //直播业务有用到只迁移了action需要后面修改云模块的修改
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin-v2/cloud-center/edu-cloud/live/trial.html.twig');
        }

        if (!$this->isVisibleCloud()) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }

        $liveCourseSetting = $this->getSettingService()->get('live-course', []);
        $client = new EdusohoLiveClient();
        $capacity = $client->getCapacity();

        if ($request->isMethod('POST')) {
            try {
                $api = CloudAPIFactory::create('root');
                $overview = $api->get('/me/live/overview');
            } catch (\RuntimeException $e) {
                return $this->render('admin-v2/cloud-center/edu-cloud/live-error.html.twig', []);
            }

            if (isset($overview['isBuy'])) {
                $this->setFlashMessage('danger', 'site.illegal.request');
            }

            $live = $request->request->all();
            $liveCourseSetting = array_merge($liveCourseSetting, $live);
            $liveCourseSetting['live_student_capacity'] = empty($capacity['capacity']) ? 0 : $capacity['capacity'];
            $liveCourseSetting['live_provider'] = empty($capacity['provider']) ? 0 : $capacity['provider'];
            $liveCourseSetting['live_provider_code'] = empty($capacity['code']) ? 0 : $capacity['code'];

            $courseSetting = $this->getSettingService()->get('course', []);
            $setting = array_merge($courseSetting, $liveCourseSetting);
            $this->getSettingService()->set('live-course', $liveCourseSetting);
            $this->getSettingService()->set('course', $setting);

            $this->setCloudLiveLogo($capacity['provider'], $client);

            $this->saveLiveCloudSetting($live);

            $this->getEduCloudService()->uploadCallbackUrl();

            $redirectUrl = in_array($capacity['provider'], ['talkFun', 'liveCloud']) ? 'admin_v2_edu_cloud_edulive_setting' : 'admin_v2_edu_cloud_edulive_overview';

            return $this->redirectToRoute($redirectUrl);
        }

        if (empty($liveCourseSetting['live_course_enabled'])) {
            return $this->redirect($this->generateUrl('admin_v2_edu_cloud_edulive_overview'));
        }

        $liveEnabled = $liveCourseSetting['live_course_enabled'];
        if (null === $liveEnabled || 0 === $liveEnabled) {
            return $this->redirect($this->generateUrl('admin_v2_edu_cloud_edulive_overview'));
        }
        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/live/overview');
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/live-error.html.twig', []);
        }

        return $this->render('admin-v2/cloud-center/edu-cloud/live/setting.html.twig', [
            'account' => $overview['account'],
            'liveCourseSetting' => $liveCourseSetting,
            'capacity' => $capacity,
            'liveCloudSetting' => $this->getSettingService()->get('live_cloud'),
        ]);
    }

    public function logoCropAction(Request $request, $type)
    {
        if (!in_array($type, ['web', 'app'])) {
            return $this->createMessageResponse('error', '参数不正确');
        }

        if ('POST' === $request->getMethod()) {
            $options = $request->request->all();

            $image = $options['images'][0];
            $file = $this->getFileService()->getFile($image['id']);

            $liveSetting = $this->getSettingService()->get('live-course', []);
            $url = $this->get('web.twig.extension')->getFurl($file['uri']);

            $oldFileId = empty($liveSetting["{$type}LogoFileId"]) ? '' : $liveSetting["{$type}LogoFileId"];
            if ($oldFileId) {
                $this->getFileService()->deleteFile($oldFileId);
            }

            $liveSetting["{$type}LogoFileId"] = $file['id'];
            $liveSetting["{$type}LogoPath"] = $url;

            $this->getSettingService()->set('live-course', $liveSetting);

            return $this->createJsonResponse(['fileId' => $file['id'], 'url' => $url, 'type' => $type]);
        }

        $fileId = $request->getSession()->get('fileId');

        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 100, 100);

        return $this->render('admin-v2/cloud-center/edu-cloud/live/logo-crop-modal.html.twig', [
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
            'type' => $type,
        ]);
    }

    //云视频概览页
    public function videoOverviewAction(Request $request)
    {
        //附件业务有用到只迁移了action需要后面修改云模块的修改
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin-v2/cloud-center/edu-cloud/video/trial.html.twig', []);
        }

        if (!($this->isVisibleCloud())) {
            return $this->redirect($this->generateUrl('admin_v2_my_cloud_overview'));
        }

        $storageSetting = $this->getSettingService()->get('storage', []);
        //云端视频判断
        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/storage/overview');
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/video-error.html.twig', []);
        }
        if ((isset($storageSetting['upload_mode']) && 'local' == $storageSetting['upload_mode']) || !isset($storageSetting['upload_mode'])) {
            return $this->render('admin-v2/cloud-center/edu-cloud/video/without-enable.html.twig');
        }

        $overview['video']['isBuy'] = isset($overview['video']['isBuy']) ? false : true;
        $overview['yearPackage']['isBuy'] = isset($overview['yearPackage']['isBuy']) ? false : true;

        $spaceItems = $this->dealItems($overview['video']['spaceItems']);
        $flowItems = $this->dealItems($overview['video']['flowItems']);

        return $this->render('admin-v2/cloud-center/edu-cloud/video/overview.html.twig', [
            'video' => $overview['video'],
            'space' => isset($overview['space']) ? $overview['space'] : null,
            'flow' => isset($overview['flow']) ? $overview['flow'] : null,
            'yearPackage' => $overview['yearPackage'],
            'spaceItems' => $spaceItems,
            'flowItems' => $flowItems,
        ]);
    }

    public function videoSettingAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin-v2/cloud-center/edu-cloud/video/trial.html.twig', []);
        }

        if (!($this->isVisibleCloud())) {
            return $this->redirect($this->generateUrl('admin_v2_my_cloud_overview'));
        }
        $storageSetting = $this->getSettingService()->get('storage', []);

        if ((isset($storageSetting['upload_mode']) && 'local' == $storageSetting['upload_mode']) || !isset($storageSetting['upload_mode'])) {
            return $this->redirect($this->generateUrl('admin_v2_edu_cloud_video_overview'));
        }

        $storageSetting = $this->getSettingService()->get('storage', []);
        $default = [
            'upload_mode' => 'local',
            'support_mobile' => 0,
            'video_h5_enable' => 1,
            'enable_playback_rates' => 0,
            'video_quality' => 'high',
            'doc_quality' => 'normal',
            'video_audio_quality' => 'high',
            'video_watermark' => 0,
            'video_watermark_image' => '',
            'video_embed_watermark_image' => '',
            'video_watermark_position' => 'topright',
            'video_fingerprint' => 0,
            'video_fingerprint_time' => 0.5,
            'video_fingerprint_opacity' => 1,
            'video_fingerprint_content' => ['domain', 'nickname'],
            'video_header' => null,
            'video_auto_play' => 'true',
        ];

        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();
            $storageSetting = array_merge($default, $storageSetting, $set);
            if (!empty($set['isDeleteMP4'])) {
                $this->deleteCloudMP4Files();
                $storageSetting['delete_mp4_status'] = 'waiting';
            }

            if (empty($set['video_fingerprint_content'])) {
                $storageSetting['video_fingerprint_content'] = [];
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
            return $this->render('admin-v2/cloud-center/edu-cloud/video-error.html.twig', []);
        }

        try {
            $headLeader = $this->getUploadFileService()->getFileByTargetType('headLeader');
        } catch (\RuntimeException $e) {
            $headLeader = null;
        }

        return $this->render('admin-v2/cloud-center/edu-cloud/video/setting.html.twig', [
            'storageSetting' => $storageSetting,
            'headLeader' => $headLeader,
            'video' => $overview['video'],
        ]);
    }

    public function videoSwitchAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();
            $storageSetting = $this->getSettingService()->get('storage', []);
            $storageSetting = array_merge($storageSetting, $set);
            $this->getSettingService()->set('storage', $storageSetting);

            return $this->redirect($this->generateUrl('admin_v2_edu_cloud_video_overview'));
        }
    }

    public function deleteVideoAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $this->deleteCloudMP4Files();

            $setting = $this->getSettingService()->get('storage', []);
            $setting['delete_mp4_status'] = 'waiting';
            $this->getSettingService()->set('storage', $setting);

            return $this->createJsonResponse(true);
        }

        $hasMp4Video = $this->getCloudFileService()->hasMp4Video();

        if (!$hasMp4Video) {
            return $this->render('admin-v2/cloud-center/edu-cloud/video/video-delete-success-modal.html.twig');
        }

        return $this->render('admin-v2/cloud-center/edu-cloud/video/video-delete-confirm-modal.html.twig');
    }

    public function showRenewVideoAction(Request $request)
    {
        $renewVideo = $request->query->get('renewVideo');

        return $this->render('admin-v2/cloud-center/edu-cloud/video/video-renew-modal.html.twig', [
            'renewVideo' => $renewVideo,
        ]);
    }

    public function headLeaderParamsAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $params = $request->query->all();

        $params['user'] = $user->id;
        $params['targetType'] = 'headLeader';
        $params['targetId'] = '0';
        $params['convertor'] = 'HLSEncryptedVideo';
        $params['videoQuality'] = 'normal';
        $params['audioQuality'] = 'normal';
        $params['convertCallback'] = null;

        $params = $this->getUploadFileService()->makeUploadParams($params);

        return $this->createJsonResponse($params);
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

        $response = [
            'path' => $path,
            'url' => $this->get('web.twig.extension')->getFileUrl($path),
        ];

        return new Response(json_encode($response));
    }

    public function videoWatermarkRemoveAction(Request $request)
    {
        return $this->createJsonResponse(true);
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

        $response = [
            'path' => $path,
            'url' => $this->get('web.twig.extension')->getFileUrl($path),
        ];

        return new Response(json_encode($response));
    }

    public function smsStatusAction(Request $request)
    {
        $dataUserPosted = $request->request->all();
        $settings = $this->getSettingService()->get('cloud_sms', []);
        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/sms/overview');
            $cloudInfo = $api->get('/me');
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/sms-error.html.twig', []);
        }

        if (isset($overview['isBuy'])) {
            $this->setFlashMessage('danger', 'site.illegal.request');
        }
        $smsStatus = $this->handleUserSmsSetting($dataUserPosted);

        if (empty($cloudInfo['accessCloud'])) {
            return $this->createMessageResponse('info', '对不起，请先接入教育云！', '', 3,
                $this->generateUrl('admin_v2_my_cloud_overview'));
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

        return $this->redirect($this->generateUrl('admin_v2_edu_cloud_sms_overview'));
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
                $result = $api->post("/sms/{$api->getAccessKey()}/apply", ['name' => $dataUserPosted['name']]);

                if (isset($result['status']) && ('ok' == $result['status'])) {
                    $this->setCloudSmsKey('sms_school_candidate_name', $dataUserPosted['name']);
                    $this->setCloudSmsKey('show_message', 'on');

                    return $this->createJsonResponse(['ACK' => 'ok']);
                }
            }

            return $this->createJsonResponse([
                'ACK' => 'failed',
                'message' => $result['error'].'|'.($this->calStrlen($dataUserPosted['name'])),
            ]);
        }

        return $this->render('admin-v2/cloud-center/edu-cloud/apply-sms-form.html.twig', []);
    }

    public function smsNoMessageAction(Request $request)
    {
        $this->setCloudSmsKey('show_message', 'off');

        return $this->redirect($this->generateUrl('admin_v2_edu_cloud_sms_overview', []));
    }

    public function emailOverviewAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin-v2/cloud-center/edu-cloud/email/trial.html.twig');
        }

        if (!($this->isVisibleCloud())) {
            return $this->redirect($this->generateUrl('admin_v2_my_cloud_overview'));
        }

        $settings = $this->getSettingService()->get('storage', []);
        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            $this->setFlashMessage('warning', 'admin.cloud.license.has_no_license');

            return $this->redirect($this->generateUrl('admin_v2_setting_cloud_key_update'));
        }

        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/email/overview');
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/email-error.html.twig', []);
        }
        $emailSettings = $this->getSettingService()->get('cloud_email_crm', []);
        $isEmailWithoutEnable = $this->isEmailWithoutEnable($overview, $emailSettings);
        if ($isEmailWithoutEnable) {
            $overview['isBuy'] = isset($overview['isBuy']) ? false : true;

            return $this->render('admin-v2/cloud-center/edu-cloud/email/without-enable.html.twig', [
                'overview' => $overview,
            ]);
        }
        $chartData = $this->dealChartData($overview['data']);

        return $this->render('admin-v2/cloud-center/edu-cloud/email/overview.html.twig', [
            'account' => $overview['account'],
            'chartData' => $chartData,
        ]);
    }

    public function emailSettingAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin-v2/cloud-center/edu-cloud/email/trial.html.twig');
        }

        if (!$this->isVisibleCloud()) {
            return $this->redirect($this->generateUrl('admin_v2_my_cloud_overview'));
        }

        $emailSettings = $this->getSettingService()->get('cloud_email_crm', []);
        if (!isset($emailSettings['status']) || (isset($emailSettings['status']) && 'disable' == $emailSettings['status'])) {
            return $this->redirect($this->generateUrl('admin_v2_edu_cloud_email_overview'));
        }

        $settings = $this->getSettingService()->get('storage', []);

        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            $this->setFlashMessage('warning', 'admin.cloud.license.has_no_license');

            return $this->redirect($this->generateUrl('admin_v2_setting_cloud_key_update'));
        }
        try {
            $api = CloudAPIFactory::create('root');
            $account = $api->get('/me/email_account');

            return $this->render('admin-v2/cloud-center/edu-cloud/email/setting.html.twig', [
                'account' => $account,
            ]);
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/email-error.html.twig', []);
        }
    }

    public function emailSwitchAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            try {
                $api = CloudAPIFactory::create('root');
                $overview = $api->get('/me/email/overview');
            } catch (\RuntimeException $e) {
                return $this->render('admin-v2/cloud-center/edu-cloud/email-error.html.twig', []);
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

            return $this->redirect($this->generateUrl('admin_v2_edu_cloud_email_overview'));
        }
    }

    public function searchOverviewAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin-v2/cloud-center/edu-cloud/search/trial.html.twig');
        }

        if (!$this->isVisibleCloud()) {
            return $this->redirect($this->generateUrl('admin_v2_my_cloud_overview'));
        }

        $cloud_search_setting = $this->getSettingService()->get('cloud_search', []);
        try {
            $api = CloudAPIFactory::create('root');

            $userOverview = $api->get("/users/{$api->getAccessKey()}/overview");
            $searchOverview = $api->get('/me/search/overview');
            $data = $this->initCloudSearch($api, $cloud_search_setting);
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/search/without-enable.html.twig', [
                'data' => ['status' => 'unlink'],
            ]);
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

            return $this->render('admin-v2/cloud-center/edu-cloud/search/overview.html.twig', [
                'searchOverview' => $searchOverview,
                'chartData' => $chartData,
            ]);
        } else {
            return $this->render('admin-v2/cloud-center/edu-cloud/search/without-enable.html.twig', [
                'data' => $data,
            ]);
        }
    }

    public function searchSettingAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin-v2/cloud-center/edu-cloud/search/trial.html.twig');
        }

        if (!($this->isVisibleCloud())) {
            return $this->redirect($this->generateUrl('admin_v2_my_cloud_overview'));
        }

        $cloudSearchSettting = $this->getSettingService()->get('cloud_search', []);
        if (!$cloudSearchSettting['search_enabled']) {
            return $this->redirect($this->generateUrl('admin_v2_edu_cloud_search_overview'));
        }

        $cloudSearchSetting = $this->getSettingService()->get('cloud_search', []);
        $searchInitStatus = $this->checkCloudSearchStatus($cloudSearchSetting);

        return $this->render('admin-v2/cloud-center/edu-cloud/search/setting.html.twig', [
            'searchInitStatus' => $searchInitStatus,
        ]);
    }

    public function searchClauseAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $callbackRouteUrl = $this->generateUrl('edu_cloud_search_callback');
            $this->getSearchService()->applySearchAccount($callbackRouteUrl);

            return $this->redirect($this->generateUrl('admin_v2_edu_cloud_search_overview'));
        }

        return $this->render('admin-v2/cloud-center/edu-cloud/cloud-search-clause-modal.html.twig');
    }

    public function searchOpenAction()
    {
        $cloud_search_setting = $this->getSettingService()->get('cloud_search', []);
        if ('ok' == $cloud_search_setting['status'] || 'waiting' == $cloud_search_setting['status']) {
            $cloud_search_setting['search_enabled'] = 1;
            $this->getSettingService()->set('cloud_search', $cloud_search_setting);
        }

        return $this->redirect($this->generateUrl('admin_v2_edu_cloud_search_overview'));
    }

    public function searchCloseAction()
    {
        $cloud_search_setting = $this->getSettingService()->get('cloud_search', []);
        $cloud_search_setting['search_enabled'] = 0;
        $this->getSettingService()->set('cloud_search', $cloud_search_setting);

        return $this->redirect($this->generateUrl('admin_v2_edu_cloud_search_overview'));
    }

    public function searchReapplyAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $callbackRouteUrl = $this->generateUrl('edu_cloud_search_callback');
            $this->getSearchService()->applySearchAccount($callbackRouteUrl);
            $this->getSearchService()->refactorAllDocuments();

            return $this->redirect($this->generateUrl('admin_v2_edu_cloud_search_overview'));
        }

        return $this->render('admin-v2/cloud-center/edu-cloud/cloud-search-reapply-modal.html.twig');
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

        return $this->redirect($this->generateUrl('admin_v2_edu_cloud_search_setting'));
    }

    public function consultSettingAction(Request $request)
    {
        if (!$this->isVisibleCloud()) {
            return $this->redirect($this->generateUrl('admin_v2_my_cloud_overview'));
        }

        try {
            $account = $this->getConsultService()->getAccount();
            $jsResource = $this->getConsultService()->getJsResource();
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/consult/consult-error.html.twig', []);
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

        return $this->render('admin-v2/cloud-center/edu-cloud/consult/setting.html.twig', [
            'cloud_consult' => $cloudConsult,
        ]);
    }

    public function attachmentSettingAction(Request $request)
    {
        //云端视频判断
        try {
            $api = CloudAPIFactory::create('root');
            $info = $api->get('/me');
            if (empty($info['accessCloud'])) {
                return $this->render('admin-v2/cloud-center/edu-cloud/not-access.html.twig', ['menu' => 'admin_v2_cloud_attachment_setting']);
            }
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/video-error.html.twig', []);
        }

        $attachment = $this->getSettingService()->get('cloud_attachment', []);
        $defaultData = ['article' => 0, 'course' => 0, 'classroom' => 0, 'group' => 0, 'question' => 0];
        $default = array_merge($defaultData, ['enable' => 0, 'fileSize' => 500]);
        $attachment = array_merge($default, $attachment);

        if ('POST' == $request->getMethod()) {
            $attachment = $request->request->all();
            $attachment = array_merge($default, $attachment);
            $this->getSettingService()->set('cloud_attachment', $attachment);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/cloud-center/edu-cloud/cloud-attachment.html.twig', [
            'attachment' => $attachment,
            'info' => $info,
        ]);
    }

    public function cloudFilesSettingAction(Request $request)
    {
        $cloudFileSetting = $this->getSettingService()->get('cloud_file_setting', []);
        $cloudFileSetting = array_merge(['enable' => 0], $cloudFileSetting);

        if ('POST' == $request->getMethod()) {
            $cloudFileSetting = $request->request->all();
            $this->getSettingService()->set('cloud_file_setting', $cloudFileSetting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/cloud-center/edu-cloud/cloud-file-setting.html.twig', [
            'cloudFileSetting' => $cloudFileSetting,
        ]);
    }

    //授权页
    public function keyAction(Request $request)
    {
        $settings = $this->getSettingService()->get('storage', []);

        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            return $this->redirect($this->generateUrl('admin_v2_setting_cloud_key_update'));
        }

        $info = [];
        try {
            $api = CloudAPIFactory::create('root');
            $info = $api->get('/me');
        } catch (\RuntimeException $e) {
            $info['error'] = 'error';
        }

        return $this->render('admin-v2/cloud-center/edu-cloud/key.html.twig', [
            'info' => $info,
        ]);
    }

    public function keyUpdateAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->redirect($this->generateUrl('admin_v2_setting_cloud_key'));
        }

        $settings = $this->getSettingService()->get('storage', []);

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

        return $this->render('admin-v2/cloud-center/edu-cloud/key-update.html.twig', []);
    }

    public function keyApplyAction(Request $request)
    {
        $applier = new KeyApplier();
        $keys = $applier->applyKey($this->getUser());

        if (empty($keys['accessKey']) || empty($keys['secretKey'])) {
            return $this->createJsonResponse(['error' => 'Key生成失败，请检查服务器网络后，重试！']);
        }

        $settings = $this->getSettingService()->get('storage', []);

        $settings['cloud_access_key'] = $keys['accessKey'];
        $settings['cloud_secret_key'] = $keys['secretKey'];
        $settings['cloud_key_applied'] = 1;

        $this->getSettingService()->set('storage', $settings);

        return $this->createJsonResponse(['status' => 'ok']);
    }

    public function keyInfoAction(Request $request)
    {
        $api = CloudAPIFactory::create('root');
        $info = $api->get('/me');

        if (!empty($info['accessKey'])) {
            $settings = $this->getSettingService()->get('storage', []);

            if (empty($settings['cloud_key_applied'])) {
                $settings['cloud_key_applied'] = 1;
                $this->getSettingService()->set('storage', $settings);
            }

            if ($info['copyright']) {
                $copyright = $this->getSettingService()->get('copyright', []);
                $copyright['owned'] = 1;
                $copyright['thirdCopyright'] = $info['thirdCopyright'];
                $copyright['licenseDomains'] = $info['licenseDomains'];
                $this->getSettingService()->set('copyright', $copyright);
            } else {
                $this->getSettingService()->delete('copyright');
            }
        } else {
            $settings = $this->getSettingService()->get('storage', []);
            $settings['cloud_key_applied'] = 0;
            $this->getSettingService()->set('storage', $settings);
        }

        $currentHost = $request->server->get('HTTP_HOST');

        if (isset($info['licenseDomains'])) {
            $info['licenseDomainCount'] = count(explode(';', $info['licenseDomains']));
        }

        return $this->render('admin-v2/cloud-center/edu-cloud/key-license-info.html.twig', [
            'info' => $info,
            'currentHost' => $currentHost,
            'isLocalAddress' => $this->isLocalAddress($currentHost),
        ]);
    }

    public function keyBindAction(Request $request)
    {
        $api = CloudAPIFactory::create('root');
        $currentHost = $request->server->get('HTTP_HOST');
        $result = $api->post('/me/license-domain', ['domain' => $currentHost]);

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

        $this->getSettingService()->set('copyright', [
            'owned' => 1,
            'name' => $request->request->get('name', ''),
            'thirdCopyright' => isset($info['thirdCopyright']) ? $info['thirdCopyright'] : 0,
            'licenseDomains' => isset($info['licenseDomains']) ? $info['licenseDomains'] : '',
        ]);

        return $this->createJsonResponse(['status' => 'ok']);
    }

    public function appImAction(Request $request)
    {
        $appImSetting = $this->getSettingService()->get('app_im', []);
        if (!$appImSetting) {
            $appImSetting = ['enabled' => 0, 'convNo' => ''];
            $this->getSettingService()->set('app_im', $appImSetting);
        }

        $data = ['status' => 'success'];

        try {
            $api = CloudAPIFactory::create('root');

            $overview = $api->get("/users/{$api->getAccessKey()}/overview");
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/video-error.html.twig', []);
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

        return $this->render('admin-v2/cloud-center/edu-cloud/app-im-setting.html.twig', [
            'data' => $data,
        ]);
    }

    public function appImUpdateStatusAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $appImSetting = $this->getSettingService()->get('app_im', []);
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
            $api->post('/me/account', ['status' => $imStatus]);

            $appImSetting['enabled'] = $status;

            //创建全站会话
            if ($status && empty($appImSetting['convNo'])) {
                $conversation = $this->getConversationService()->createConversation('全站会话', 'global', 0, [$user]);
                if ($conversation) {
                    $appImSetting['convNo'] = $conversation['no'];
                }
            }

            $this->getSettingService()->set('app_im', $appImSetting);

            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
    }

    protected function isLocalAddress($address)
    {
        if (in_array($address, ['localhost', '127.0.0.1'])) {
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

    protected function saveLiveCloudSetting($setting)
    {
        $liveCloudSetting = $this->getSettingService()->get('live_cloud', []);
        $liveCloudSetting = array_merge($liveCloudSetting, ArrayToolkit::parts($setting, ['live_watermark_enable', 'live_watermark_info']));
        if (empty($setting['live_watermark_info'])) {
            unset($liveCloudSetting['live_watermark_info']);
        }
        $this->getSettingService()->set('live_cloud', $liveCloudSetting);
    }

    private function renderConsultWithoutEnable($cloudConsult)
    {
        return $this->render('admin-v2/cloud-center/edu-cloud/consult/without-enable.html.twig', [
            'cloud_consult' => $cloudConsult,
        ]);
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
            $data = [
                'search_enabled' => 0,
                'status' => 'closed', //'closed':未开启；'waiting':'索引中';'ok':'索引完成'
            ];
        }

        if (empty($data['status'])) {
            $data['status'] = 'closed';
        }

        if ('waiting' == $data['status']) {
            $search_account = $api->get('/me/search_account');

            if ('yes' == $search_account['isInit']) {
                $data = [
                    'search_enabled' => $data['search_enabled'],
                    'status' => 'ok',
                ];
            }
        }
        if (empty($data['type'])) {
            $data['type'] = [
                'course' => 1,
                'classroom' => 1,
                'teacher' => 1,
                'thread' => 1,
                'article' => 1,
            ];
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
        $setting = $this->getSettingService()->get('cloud_sms', []);
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

        $callback = $this->get('request')->getSchemeAndHttpHost().$this->generateUrl('callback', ['type' => 'cloudFile', 'ac' => 'files.notify']);

        $this->getCloudFileService()->deleteCloudMP4Files($user['id'], $callback);

        return true;
    }

    protected function handleSmsSetting(Request $request, $api)
    {
        $dataUserPosted = $request->request->all();
        $settings = $this->getSettingService()->get('cloud_sms', []);

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
        $defaultSetting = [
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
            'sms_comment_modify' => 'off',
        ];

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
        $setting = $this->getSettingService()->get('live-course', []);

        $isSetLogo = !empty($setting['webLogoPath']) || !empty($setting['appLogoPath']) || !empty($setting['logoUrl']);

        if ('talkFun' == $provider && $isSetLogo) {
            $logoData = [
                'logoPcUrl' => empty($setting['webLogoPath']) ? '' : $setting['webLogoPath'],
                'logoClientUrl' => empty($setting['appLogoPath']) ? '' : $setting['appLogoPath'],
                'logoGotoUrl' => empty($setting['logoUrl']) ? 'http://www.talk-fun.com' : $setting['logoUrl'],
            ];
            $result = $client->setLiveLogo($logoData);

            if (isset($result['error'])) {
                return $this->createMessageResponse('error', '设置直播logo出错');
            }
        }

        return true;
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

    protected function getCloudCenterExperiencePage()
    {
        $trial = file_get_contents('http://open.edusoho.com/api/v1/block/experience');
        $trialHtml = json_decode($trial, true);

        return $trialHtml;
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

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return CloudFileService
     */
    protected function getCloudFileService()
    {
        return $this->createService('CloudFile:CloudFileService');
    }

    /**
     * @return SearchService
     */
    protected function getSearchService()
    {
        return $this->createService('Search:SearchService');
    }

    /**
     * @return MicroyanConsultServiceImpl
     */
    protected function getConsultService()
    {
        return $this->createService('EduCloud:MicroyanConsultService');
    }

    /**
     * @return ConversationService
     */
    protected function getConversationService()
    {
        return $this->createService('IM:ConversationService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }
}
