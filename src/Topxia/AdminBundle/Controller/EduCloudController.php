<?php

namespace Topxia\AdminBundle\Controller;

use Imagine\Image\Box;
use Imagine\Gd\Imagine;
use Topxia\Common\Paginator;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Topxia\Service\CloudPlatform\Client\EduSohoOpenClient;

class EduCloudController extends BaseController
{
    public function indexAction(Request $request)
    {
        // try {
        //     $api = CloudAPIFactory::create('root');

        //     $account = $api->get('/accounts');

        //     if (!empty($account)) {
        //         $money = isset($account['cash']) ? $account['cash'] : '--';

        //         $loginToken = $this->getAppService()->getLoginToken();

        //         $result = $api->post("/sms/{$api->getAccessKey()}/applyResult");

        //         if (isset($result['apply']) && isset($result['apply']['status'])) {
        //             $smsStatus['status']  = $result['apply']['status'];
        //             $smsStatus['message'] = $result['apply']['message'];
        //         } elseif (isset($result['error'])) {
        //             $smsStatus['status']  = 'error';
        //             $smsStatus['message'] = $result['error'];
        //         }
        //     }
        // } catch (\RuntimeException $e) {
        //     return $this->render('TopxiaAdminBundle:EduCloud:api-error.html.twig', array());
        // }

        // return $this->render('TopxiaAdminBundle:EduCloud:edu-cloud.html.twig', array(
        //     'account'   => $account,
        //     'token'     => isset($loginToken) && isset($loginToken["token"]) ? $loginToken["token"] : '',
        //     'smsStatus' => isset($smsStatus) ? $smsStatus : null
        // ));
    }

    //概览页，产品简介页
    public function myCloudAction(Request $request)
    {
        // @apitodo 需改成leaf
        $api = CloudAPIFactory::create('root');

        $content = $api->get("/users/{$api->getAccessKey()}/overview");

        $info = $api->get('/me');

        $eduSohoOpenClient = new EduSohoOpenClient();

        if (empty($info['level']) || (!(isset($content['service']['storage'])) && !(isset($content['service']['live'])) && !(isset($content['service']['sms'])))) {
            $articles = $eduSohoOpenClient->getArticles();
            $articles = json_decode($articles, true);

            if ($this->getWebExtension()->isTrial()) {
                $trialHtml = $this->getCloudCenterExperiencePage();
                return $this->render('TopxiaAdminBundle:EduCloud:cloud.html.twig', array(
                    'articles' => $articles,
                    'trial'    => $trialHtml['content']
                ));
            }

            $unTrial     = file_get_contents('http://open.edusoho.com/api/v1/block/cloud_guide');
            $unTrialHtml = json_decode($unTrial, true);
            return $this->render('TopxiaAdminBundle:EduCloud:cloud.html.twig', array(
                'articles' => $articles,
                'untrial'  => $unTrialHtml['content']
            ));
        }

        return $this->redirect($this->generateUrl("admin_my_cloud_overview"));
    }

    //概览页，服务概况页
    public function myCloudOverviewAction(Request $request)
    {
        try {
            $api = CloudAPIFactory::create('root');
            //$api->setApiUrl('http://124.160.104.74:8098/');
            $info = $api->get('/me');
            var_dump($info);

            if (isset($info['licenseDomains'])) {
                $info['licenseDomainCount'] = count(explode(';', $info['licenseDomains']));
            }

            $isBinded = $this->getAppService()->getBinded();

            $isBinded['email'] = isset($isBinded['email']) ? str_replace(substr(substr($isBinded['email'], 0, stripos($isBinded['email'], '@')), -4), '****', $isBinded['email']) : null;

            $eduSohoOpenClient = new EduSohoOpenClient;
            $content           = $api->get("/user/center/{$api->getAccessKey()}/overview");
            //var_dump($content);
        } catch (\RuntimeException $e) {
            return $this->render('TopxiaAdminBundle:EduCloud:cloud-error.html.twig', array());
        }

        //$content = $this->getContent();
        // var_dump($content);
        $cashInfo   = isset($content['cashInfo']) ? $content['cashInfo'] : null;
        $couponInfo = isset($content['couponInfo']) ? $content['couponInfo'] : null;
        $videoInfo  = isset($content['vlseInfo']['videoInfo']) ? $content['vlseInfo']['videoInfo'] : null;
        $liveInfo   = isset($content['vlseInfo']['liveInfo']) ? $content['vlseInfo']['liveInfo'] : null;
        $smsInfo    = isset($content['vlseInfo']['smsInfo']) ? $content['vlseInfo']['smsInfo'] : null;
        $emailInfo  = isset($content['vlseInfo']['emailInfo']) ? $content['vlseInfo']['emailInfo'] : null;

        // videoUsedInfo测试数据
        // $videoUsedInfo = '[{"date":"2015-03","count":99},{"date":"2015-04","count":9},{"date":"2015-05","count":77},{"date":"2015-06","count":10},{"date":"2015-07","count":40},{"date":"2015-08","count":30},{"date":"2015-09","count":20}]';
        $chartInfo = array(
            'videoUsedInfo' => $this->generateChartData($videoInfo['usedInfo']),
            'smsUsedInfo'   => $this->generateChartData($smsInfo['usedInfo']),
            'liveUsedInfo'  => $this->generateChartData($liveInfo['usedInfo']),
            'emailUsedInfo' => $this->generateChartData($emailInfo['usedInfo'])
        );

        $notices = $eduSohoOpenClient->getNotices();
        $notices = json_decode($notices, true);

        if ($this->getWebExtension()->isTrial()) {
            $trialHtml = $this->getCloudCenterExperiencePage();
        }

        return $this->render('TopxiaAdminBundle:EduCloud:my-cloud.html.twig', array(
            'locked'     => isset($info['locked']) ? $info['locked'] : 0,
            'enabled'    => isset($info['enabled']) ? $info['enabled'] : 1,
            'notices'    => $notices,
            'isBinded'   => $isBinded,
            'cashInfo'   => $cashInfo,
            'couponInfo' => $couponInfo,
            'videoInfo'  => $videoInfo,
            'liveInfo'   => $liveInfo,
            'smsInfo'    => $smsInfo,
            'emailInfo'  => $emailInfo,
            'chartInfo'  => $chartInfo
        ));
    }

    //云视频设置页
    public function videoAction(Request $request)
    {
        $storageSetting = $this->getSettingService()->get('storage', array());
        $default        = array(
            'upload_mode'                 => 'local',
            'cloud_bucket'                => '',
            'video_quality'               => 'low',
            'video_audio_quality'         => 'low',
            'video_watermark'             => 0,
            'video_watermark_image'       => '',
            'video_embed_watermark_image' => '',
            'video_watermark_position'    => 'topright',
            'video_fingerprint'           => 0,
            'video_header'                => null
        );

        if ($request->getMethod() == 'POST') {
            $set = $request->request->all();

            if (isset($set['cloud_bucket'])) {
                $set['cloud_bucket'] = trim($set['cloud_bucket']);
            }

            $storageSetting = array_merge($default, $storageSetting, $set);
            $this->getSettingService()->set('storage', $storageSetting);
            $this->setFlashMessage('success', '云视频设置已保存！');
        } else {
            $storageSetting = array_merge($default, $storageSetting);
        }

        //云端视频判断
        try {
            $api = CloudAPIFactory::create('root');
            $api->setApiUrl('http://124.160.104.74:8098/');
            $info = $api->get('/me');
        } catch (\RuntimeException $e) {
            return $this->render('TopxiaAdminBundle:EduCloud:video-error.html.twig', array());
        }

        // $content = $api->get("/user/center/{$api->getAccessKey()}/overview");
        // $content   = $this->getContent();
        $videoInfo = isset($content['vlseInfo']['videoInfo']) ? $content['vlseInfo']['videoInfo'] : null;

        $headLeader = array();

        if (!empty($storageSetting) && array_key_exists("video_header", $storageSetting) && $storageSetting["video_header"]) {
            $headLeader = $this->getUploadFileService()->getFileByTargetType('headLeader');
        }

        return $this->render('TopxiaAdminBundle:EduCloud:video.html.twig', array(
            'storageSetting' => $storageSetting,
            'headLeader'     => $headLeader,
            'videoInfo'      => $videoInfo,
            'info'           => $info
        ));
    }

    public function videoControlAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $set            = $request->request->all();
            $storageSetting = $this->getSettingService()->get('storage', array());
            $storageSetting = array_merge($storageSetting, $set);
            $this->getSettingService()->set('storage', $storageSetting);
            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
    }

    //此方法做测试用，离线数据
    public function getContent()
    {
        $content             = array();
        $content['cashInfo'] = array(
            'cash'          => '13737',
            'arrearageDays' => '230'
        );
        $content['couponInfo'] = array(
            'availableMoney' => '99.00'
        );
        $vlseInfo              = array();
        $vlseInfo['videoInfo'] = array(
            'userId'         => '13737',
            'startMouth'     => '201512',
            'endMouth'       => '201611',
            'freeTransfer'   => '100.00',
            'freeSpace'      => '100.00',
            'amount'         => '24.00',
            'enableBuyVideo' => 1,
            'renewVideo'     => array(
                'userId'        => '13737',
                'effectiveDate' => '1480521600'
            ),
            'videoBill'      => null,
            'firstday'       => '1448899200',
            'lastday'        => '1480435200',
            'remaining'      => 337,
            'tlp'            => '0',
            'usedInfo'       => array(
                '2015-12-22' => '7',
                '2015-12-23' => '9',
                '2015-12-24' => '75',
                '2015-12-25' => '89',
                '2015-12-26' => '13',
                '2015-12-27' => '8',
                '2015-12-28' => '9'
            )

        );
        $vlseInfo['liveInfo'] = array(
            'userId'      => '13737',
            'capacity'    => '100',
            'expire'      => '1453478400',
            'renewInfo'   => array('effectiveDate' => '1453564800'),
            'upgradeInfo' => array(),
            'usedInfo'    => array(
                '2015-12-22' => '37',
                '2015-12-23' => '9',
                '2015-12-24' => '55',
                '2015-12-25' => '69',
                '2015-12-26' => '19',
                '2015-12-27' => '86',
                '2015-12-28' => '84'
            )

        );
        $vlseInfo['smsInfo'] = array(
            'remainCount' => '2000',
            'status'      => 'used',
            'usedInfo'    => array(
                '2015-12-22' => '47',
                '2015-12-23' => '95',
                '2015-12-24' => '65',
                '2015-12-25' => '9',
                '2015-12-26' => '18',
                '2015-12-27' => '89',
                '2015-12-28' => '86'
            )
        );
        $content['vlseInfo'] = $vlseInfo;
        return $content;
    }

    public function videoWatermarkUploadAction(Request $request)
    {
        $file = $request->files->get('watermark');

        if (!FileToolkit::isImageFile($file)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }

        $filename = 'watermark_'.time().'.'.$file->getClientOriginalExtension();

        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/system";
        $file      = $file->move($directory, $filename);
        $path      = "system/{$filename}";

        $response = array(
            'path' => $path,
            'url'  => $this->get('topxia.twig.web_extension')->getFileUrl($path)
        );

        return new Response(json_encode($response));
    }

    public function videoEmbedWatermarkUploadAction(Request $request)
    {
        $file = $request->files->get('watermark');

        if (!FileToolkit::isImageFile($file)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }

        $filename = 'watermarkembed_'.time().'.'.$file->getClientOriginalExtension();

        $directory      = "{$this->container->getParameter('topxia.upload.public_directory')}/system";
        $file           = $file->move($directory, $filename);
        $path           = "system/{$filename}";
        $originFileInfo = getimagesize($file);
        $filePath       = $this->container->getParameter('topxia.upload.public_directory')."/".$path;
        $imagine        = new Imagine();
        $rawImage       = $imagine->open($filePath);

        $pathinfo              = pathinfo($filePath);
        $specification['240']  = 20;
        $specification['360']  = 30;
        $specification['480']  = 40;
        $specification['720']  = 60;
        $specification['1080'] = 90;

        foreach ($specification as $key => $value) {
            $width        = ($originFileInfo[0] * $value / $originFileInfo[1]);
            $specialImage = $rawImage->copy();
            $specialImage->resize(new Box($width, $value));
            $filePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}-{$key}.{$pathinfo['extension']}";
            $specialImage->save($filePath);
        }

        $response = array(
            'path' => $path,
            'url'  => $this->get('topxia.twig.web_extension')->getFileUrl($path)
        );

        return new Response(json_encode($response));
    }

    public function videoWatermarkRemoveAction(Request $request)
    {
        return $this->createJsonResponse(true);
    }

    //云短信设置页
    public function smsAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('TopxiaAdminBundle:EduCloud:sms.html.twig', array());
        }

        $settings = $this->getSettingService()->get('storage', array());

        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            $this->setFlashMessage('warning', '您还没有授权码，请先绑定。');
            return $this->redirect($this->generateUrl('admin_setting_cloud_key_update'));
        }

        try {
            // $info    = $api->get('/sms/account');
            // $allInfo = $api->get('/me');
            // var_dump($info);
            //var_dump($allInfo);
            $smsStatus = $this->newHandleSmsSetting($request);

            //$smsStatus = $this->handleSmsSetting($request);
            //var_dump($smsStatus);
            return $this->render('TopxiaAdminBundle:EduCloud:sms.html.twig', array(
                'smsStatus' => $smsStatus
            ));
        } catch (\RuntimeException $e) {
            return $this->render('TopxiaAdminBundle:EduCloud:sms-error.html.twig', array());
        }
    }

    //云邮件设置页
    public function emailAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('TopxiaAdminBundle:EduCloud:email.html.twig');
        }

        $settings = $this->getSettingService()->get('storage', array());

        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            $this->setFlashMessage('warning', '您还没有授权码，请先绑定。');
            return $this->redirect($this->generateUrl('admin_setting_cloud_key_update'));
        }

        try {
            $api = CloudAPIFactory::create('root');
            //$api->setApiUrl('http://124.160.104.74:8098/');
            $info        = $api->get('/me');
            $emailStatus = $this->handleEmailSetting($request);
            return $this->render('TopxiaAdminBundle:EduCloud:email.html.twig', array(
                'locked'      => isset($info['locked']) ? $info['locked'] : 0,
                'enabled'     => isset($info['enabled']) ? $info['enabled'] : 1,
                'emailStatus' => $emailStatus
            ));
        } catch (\RuntimeException $e) {
            return $this->render('TopxiaAdminBundle:EduCloud:email-error.html.twig', array());
        }
    }

    public function applyForSmsAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $result         = null;
            $dataUserPosted = $request->request->all();

            if (
                isset($dataUserPosted['name'])
                && ($this->calStrlen($dataUserPosted['name']) >= 2)
                && ($this->calStrlen($dataUserPosted['name']) <= 16)
            ) {
                $api    = CloudAPIFactory::create('root');
                $result = $api->post("/sms/{$api->getAccessKey()}/apply", array('name' => $dataUserPosted['name']));

                if (isset($result['status']) && ($result['status'] == 'ok')) {
                    $this->setCloudSmsKey('sms_school_candidate_name', $dataUserPosted['name']);
                    $this->setCloudSmsKey('show_message', 'on');
                    return $this->createJsonResponse(array('ACK' => 'ok'));
                }
            }

            return $this->createJsonResponse(array(
                'ACK'     => 'failed',
                'message' => $result['error'].'|'.($this->calStrlen($dataUserPosted['name']))
            ));
        }

        return $this->render('TopxiaAdminBundle:EduCloud:apply-sms-form.html.twig', array());
    }

    public function smsNoMessageAction(Request $request)
    {
        $this->setCloudSmsKey('show_message', 'off');
        return $this->redirect($this->generateUrl('admin_edu_cloud_sms', array()));
    }

    public function smsBillAction(Request $request)
    {
        try {
            $api = CloudAPIFactory::create('root');

            $loginToken = $this->getAppService()->getLoginToken();
            $account    = $api->get('/accounts');

            $result = $api->get('/bills', array('type' => 'sms', 'page' => 1, 'limit' => 20));

            $paginator = new Paginator(
                $this->get('request'),
                $result["total"],
                20
            );

            $result = $api->get('/bills', array(
                'type'  => 'sms',
                'page'  => $paginator->getCurrentPage(),
                'limit' => $paginator->getPerPageCount()
            ));

            $bills = $result['items'];
        } catch (\RuntimeException $e) {
            return $this->render('TopxiaAdminBundle:EduCloud:sms-error.html.twig', array());
        }

        return $this->render('TopxiaAdminBundle:EduCloud:sms-bill.html.twig', array(
            'account'   => $account,
            'token'     => isset($loginToken) && isset($loginToken["token"]) ? $loginToken["token"] : '',
            'bills'     => $bills,
            'paginator' => $paginator
        ));
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
            $api->setApiUrl('http://124.160.104.74:8098/');
            $info = $api->get('/me');
            var_dump($info);

            // if (isset($info['levelName'])) {
            //     $info['level'] = $this->infoLevel($info['level']);
            // }
        } catch (\RuntimeException $e) {
            $info['error'] = 'error';
        }

        return $this->render('TopxiaAdminBundle:EduCloud:key.html.twig', array(
            'info' => $info
        ));
    }

    public function keyInfoAction(Request $request)
    {
        $api = CloudAPIFactory::create('root');
        $api->setApiUrl('http://124.160.104.74:8098/');
        $info = $api->get('/me');

        if (!empty($info['accessKey'])) {
            $settings = $this->getSettingService()->get('storage', array());

            if (empty($settings['cloud_key_applied'])) {
                $settings['cloud_key_applied'] = 1;
                $this->getSettingService()->set('storage', $settings);
            }

            $this->refreshCopyright($info);
        } else {
            $settings                      = $this->getSettingService()->get('storage', array());
            $settings['cloud_key_applied'] = 0;
            $this->getSettingService()->set('storage', $settings);
        }

        $currentHost = $request->server->get('HTTP_HOST');

        if (isset($info['licenseDomains'])) {
            $info['licenseDomainCount'] = count(explode(';', $info['licenseDomains']));
        }

        return $this->render('TopxiaAdminBundle:EduCloud:key-license-info.html.twig', array(
            'info'           => $info,
            'currentHost'    => $currentHost,
            'isLocalAddress' => $this->isLocalAddress($currentHost)
        ));
    }

    public function keyBindAction(Request $request)
    {
        $api         = CloudAPIFactory::create('root');
        $currentHost = $request->server->get('HTTP_HOST');
        $result      = $api->post('/me/license-domain', array('domain' => $currentHost));

        if (!empty($result['licenseDomains'])) {
            $this->setFlashMessage('success', '授权域名绑定成功！');
        } else {
            $this->setFlashMessage('danger', '授权域名绑定失败，请重试！');
        }

        return $this->createJsonResponse($result);
    }

    public function keyUpdateAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->redirect($this->generateUrl('admin_setting_cloud_key'));
        }

        $settings = $this->getSettingService()->get('storage', array());

        if ($request->getMethod() == 'POST') {
            $options = $request->request->all();

            $api = CloudAPIFactory::create('root');
            $api->setApiUrl('http://115.29.78.158:10001/');
            $api->setKey($options['accessKey'], $options['secretKey']);

            $result = $api->post(sprintf('/keys/%s/verification', $options['accessKey']));

            if (isset($result['error'])) {
                $this->setFlashMessage('danger', 'AccessKey / SecretKey　不正确！');
                goto render;
            }

            $user = $api->get('/me');

            if ($user['edition'] != 'opensource') {
                $this->setFlashMessage('danger', 'AccessKey / SecretKey　不正确！！');
                goto render;
            }

            $settings['cloud_access_key']  = $options['accessKey'];
            $settings['cloud_secret_key']  = $options['secretKey'];
            $settings['cloud_key_applied'] = 1;

            $this->getSettingService()->set('storage', $settings);

            $this->setFlashMessage('success', '授权码保存成功！');
            return $this->redirect($this->generateUrl('admin_setting_cloud_key'));
        }

        render:
        return $this->render('TopxiaAdminBundle:EduCloud:key-update.html.twig', array(
        ));
    }

    public function keyApplyAction(Request $request)
    {
        $applier = new KeyApplier();
        $keys    = $applier->applyKey($this->getCurrentUser());

        if (empty($keys['accessKey']) || empty($keys['secretKey'])) {
            return $this->createJsonResponse(array('error' => 'Key生成失败，请检查服务器网络后，重试！'));
        }

        $settings = $this->getSettingService()->get('storage', array());

        $settings['cloud_access_key']  = $keys['accessKey'];
        $settings['cloud_secret_key']  = $keys['secretKey'];
        $settings['cloud_key_applied'] = 1;

        $this->getSettingService()->set('storage', $settings);

        return $this->createJsonResponse(array('status' => 'ok'));
    }

    public function keyCopyrightAction(Request $request)
    {
        $api  = CloudAPIFactory::create('leaf');
        $info = $api->get('/me');

        if (empty($info['copyright'])) {
            throw $this->createAccessDeniedException('您无权操作!');
        }

        $name = $request->request->get('name');

        $this->getSettingService()->set('copyright', array(
            'owned'          => 1,
            'name'           => $request->request->get('name', ''),
            'thirdCopyright' => isset($info['thirdCopyright']) && $info['thirdCopyright'] == '1' ? 1 : 0
        ));

        return $this->createJsonResponse(array('status' => 'ok'));
    }

    protected function newHandleSmsSetting(Request $request)
    {
        $api = CloudAPIFactory::create('root');
        $api->setApiUrl('http://115.29.78.158:10001/');
        $defaultSetting = array(
            'sms_enabled'               => '0',
            'sms_registration'          => 'off',
            'sms_forget_password'       => 'off',
            'sms_user_pay'              => 'off',
            'sms_forget_pay_password'   => 'off',
            'sms_bind'                  => 'off',
            'sms_classroom_publish'     => 'off',
            'sms_course_publish'        => 'off',
            'sms_normal_lesson_publish' => 'off',
            'sms_live_lesson_publish'   => 'off',
            'sms_live_play_one_day'     => 'off',
            'sms_live_play_one_hour'    => 'off',
            'sms_homework_check'        => 'off',
            'sms_testpaper_check'       => 'off',
            'sms_order_pay_success'     => 'off',
            'sms_course_buy_notify'     => 'off',
            'sms_classroom_buy_notify'  => 'off',
            'sms_vip_buy_notify'        => 'off',
            'sms_coin_buy_notify'       => 'off'
        );
        $dataUserPosted = $request->request->all();

        if (isset($dataUserPosted['sms_order_pay_success']) && $dataUserPosted['sms_order_pay_success'] == 'on') {
            $dataUserPosted['sms_course_buy_notify']    = 'on';
            $dataUserPosted['sms_classroom_buy_notify'] = 'on';
            $dataUserPosted['sms_vip_buy_notify']       = 'on';
            $dataUserPosted['sms_coin_buy_notify']      = 'on';
        } else {
            $dataUserPosted['sms_course_buy_notify']    = 'off';
            $dataUserPosted['sms_classroom_buy_notify'] = 'off';
            $dataUserPosted['sms_vip_buy_notify']       = 'off';
            $dataUserPosted['sms_coin_buy_notify']      = 'off';
        }

        $settings  = $this->getSettingService()->get('cloud_sms', array());
        $smsStatus = array();
        //启用云短信，没有则创建云平台短信服务帐号

        if (isset($dataUserPosted['sms-open'])) {
            if (isset($settings['sms_school_name'])) {
                $status                   = $api->get('/sms/account');
                $smsStatus['status']      = isset($status['status']) ? $status['status'] : 'error';
                $smsStatus['sms_enabled'] = '1';
                $smsStatus                = ArrayToolkit::filter($smsStatus, $defaultSetting);
                $smsStatus                = array_merge($settings, $smsStatus);
                $this->getSettingService()->set('cloud_sms', $smsStatus);
                return $smsStatus;
            } else {
                $info = $api->post('/sms/account', array('name' => isset($dataUserPosted['sign']) ? $dataUserPosted['sign'] : $settings['sms_school_name']));

                if ($info['status'] == 'ok') {
                    $status              = $api->get('/sms/account');
                    $smsStatus['status'] = isset($status['status']) ? $status['status'] : 'error';
                    var_dump($status);
                    $smsStatus['sms_enabled']     = '1';
                    $smsStatus                    = ArrayToolkit::filter($smsStatus, $defaultSetting);
                    $smsStatus                    = array_merge($settings, $smsStatus);
                    $smsStatus['sms_school_name'] = $status['name'];
                    $this->getSettingService()->set('cloud_sms', $smsStatus);
                    return $smsStatus;
                }
            }
        }

        //关闭云短信

        if (isset($dataUserPosted['sms-close'])) {
            $status                   = $api->get('/sms/account');
            $smsStatus['status']      = isset($status['status']) ? $status['status'] : 'error';
            $smsStatus['sms_enabled'] = '0';
            $smsStatus                = ArrayToolkit::filter($smsStatus, $defaultSetting);
            $smsStatus                = array_merge($settings, $smsStatus);
            $this->getSettingService()->set('cloud_sms', $smsStatus);
            return $smsStatus;
        }

        //var_dump($dataUserPosted);
        //更新云短信签名

        if (isset($dataUserPosted['sign'])) {
            if (empty($dataUserPosted['sign'])) {
                $dataUserPosted['sign'] = $settings['sms_school_name'];
            }

            $info = $api->post('/sms/account/me', array('name' => $dataUserPosted['sign']));

            if ($info['status'] == 'ok') {
                $status                       = $api->get('/sms/account');
                $smsStatus['status']          = isset($status['status']) ? $status['status'] : 'error';
                $smsStatus['sms_enabled']     = '1';
                $smsStatus                    = ArrayToolkit::filter($smsStatus, $defaultSetting);
                $smsStatus                    = array_merge($settings, $smsStatus);
                $smsStatus['sms_school_name'] = $status['name'];
                $this->getSettingService()->set('cloud_sms', $smsStatus);
                return $smsStatus;
            }
        }

        $status              = $api->get('/sms/account');
        $smsStatus['status'] = isset($status['status']) ? $status['status'] : 'error';
        $smsStatus           = array_merge($settings, $dataUserPosted);
        $this->getSettingService()->set('cloud_sms', $smsStatus);
        var_dump($smsStatus);
        return $smsStatus;
    }

    protected function handleSmsSetting(Request $request)
    {
        list($smsStatus, $schoolNames) = $this->getSchoolName();
        $smsInfo                       = $this->getSettingService()->get('cloud_sms', array());

        if ($request->getMethod() == 'POST') {
            $dataUserPosted = $request->request->all();
            $defaultSetting = array(
                'sms_enabled'               => '0',
                'sms_registration'          => 'off',
                'sms_forget_password'       => 'off',
                'sms_user_pay'              => 'off',
                'sms_forget_pay_password'   => 'off',
                'sms_bind'                  => 'off',
                'sms_classroom_publish'     => 'off',
                'sms_course_publish'        => 'off',
                'sms_normal_lesson_publish' => 'off',
                'sms_live_lesson_publish'   => 'off',
                'sms_live_play_one_day'     => 'off',
                'sms_live_play_one_hour'    => 'off',
                'sms_homework_check'        => 'off',
                'sms_testpaper_check'       => 'off',
                'sms_order_pay_success'     => 'off',
                'sms_course_buy_notify'     => 'off',
                'sms_classroom_buy_notify'  => 'off',
                'sms_vip_buy_notify'        => 'off',
                'sms_coin_buy_notify'       => 'off'
            );

            if ($dataUserPosted['sms_order_pay_success'] == 'on') {
                $dataUserPosted['sms_course_buy_notify']    = 'on';
                $dataUserPosted['sms_classroom_buy_notify'] = 'on';
                $dataUserPosted['sms_vip_buy_notify']       = 'on';
                $dataUserPosted['sms_coin_buy_notify']      = 'on';
            } else {
                $dataUserPosted['sms_course_buy_notify']    = 'off';
                $dataUserPosted['sms_classroom_buy_notify'] = 'off';
                $dataUserPosted['sms_vip_buy_notify']       = 'off';
                $dataUserPosted['sms_coin_buy_notify']      = 'off';
            }

            $dataUserPosted = ArrayToolKit::filter($dataUserPosted, $defaultSetting);
            $dataUserPosted = array_merge($dataUserPosted, $schoolNames);
            var_dump($dataUserPosted);
            $this->getSettingService()->set('cloud_sms', $dataUserPosted);

            if ('1' == $dataUserPosted['sms_enabled']) {
                $this->setFlashMessage('success', '短信功能开启成功，短信最低￥0.055/条。');
            } else {
                $this->setFlashMessage('success', '设置成功。');
            }
        }

        return $smsStatus;
    }

    protected function handleEmailSetting(Request $request)
    {
        $dataUserPosted           = $request->request->all();
        list($emailStatus, $sign) = $this->getSign($dataUserPosted);

        $emailStatus = array_merge($emailStatus, $sign);

        if ($emailStatus['status'] != 'error') {
            $this->getSettingService()->set('cloud_email', $emailStatus);
        }

        return $emailStatus;
    }

    protected function getSign($operation)
    {
        $api = CloudAPIFactory::create('root');
        $api->setApiUrl('http://124.160.104.74:8098/');
        $settings = $this->getSettingService()->get('cloud_email', array());
        $result   = array();
        $sign     = array();

        if (isset($operation['email-open'])) {
            $result = $api->post("/me/email_account");
            $this->setFlashMessage('success', '云邮件设置已保存！');
            $mailer = $this->getSettingService()->get('mailer', array());

            if (isset($result['status']) && $result['status'] == 'enable' && $mailer['enabled'] == "1") {
                $default = array(
                    'enabled'  => 0,
                    'host'     => '',
                    'port'     => '',
                    'username' => '',
                    'password' => '',
                    'from'     => '',
                    'name'     => ''
                );
                $mailer            = array_merge($default, $mailer);
                $mailer['enabled'] = 0;
                $this->getSettingService()->set('mailer', $mailer);
                $mailerWithoutPassword             = $mailer;
                $mailerWithoutPassword['password'] = '******';
                $this->getLogService()->info('system', 'update_settings', "开启云短信关闭第三方邮件服务器设置", $mailerWithoutPassword);
            }
        } elseif (isset($operation['sign']) && !empty($operation['sign'])) {
            $params = array(
                'sender' => $operation['sign']
            );
            $result = $api->post("/me/email_account", $params);
            $this->setFlashMessage('success', '云邮件设置已保存！');
        } elseif (isset($operation['email-close'])) {
            $result = $api->delete("/me/email_account");
            $this->setFlashMessage('success', '云邮件设置已保存！');
        } elseif (empty($operation)) {
            $result                = $api->get("/me");
            $emailStatus['status'] = isset($settings['status']) ? $settings['status'] : 'error';
            $sign                  = isset($settings['sign']) ? array('sign' => $settings['sign']) : array('sign' => "");
        }

        if (isset($result['id']) && $result['id'] == 1) {
            $emailStatus = array('status' => $result['status']);
            $sign        = array('sign' => $result['nickname']);
        }

        if (isset($result['error'])) {
            $emailStatus['status'] = 'error';
            $emailStatus['msg']    = $result['error'];
        }

        return array($emailStatus, $sign);
    }

    protected function getSchoolName()
    {
        $schoolName          = $this->setting('cloud_sms.sms_school_name');
        $schoolCandidateName = $this->setting('cloud_sms.sms_school_candidate_name');
        // var_dump($schoolName);
        // var_dump($schoolCandidateName);
        $api = CloudAPIFactory::create('root');
        $api->setApiUrl('http://115.29.78.158:10001/');
        $result = $api->post("/sms/{$api->getAccessKey()}/applyResult");
        // var_dump($result);
        $smsStatus = array();

        if (isset($result['apply']) && isset($result['apply']['status'])) {
            $smsStatus['status'] = $result['apply']['status'];

            if (($smsStatus['status'] == 'passed') && (strlen($schoolCandidateName) > 0)) {
                $schoolName          = $schoolCandidateName;
                $schoolCandidateName = '';
                $this->setCloudSmsKey('sms_school_name', $schoolName);
                $this->setCloudSmsKey('sms_school_candidate_name', '');
            }

            if (isset($result['apply']['message'])) {
                $smsStatus['message'] = $result['apply']['message'];

                if (strlen($smsStatus['message']) > 0) {
                    $smsStatus['message'] = $smsStatus['message'];
                }
            }

            if ($smsStatus['status'] == 'failed') {
                $info = '您新申请的网校名称“'.$schoolCandidateName.'”未通过审核，原因是：';

                if (isset($smsStatus['message']) && $smsStatus['message']) {
                    $info .= $smsStatus['message'];
                } else {
                    $info .= '网校名称不符合规范';
                }

                $smsStatus['schoolNameError'] = $info;
            }
        } elseif (isset($result['error'])) {
            $smsStatus['status']  = 'error';
            $smsStatus['message'] = $result['error'];
        }

        return array(
            $smsStatus,
            array(
                'sms_school_name'           => $schoolName,
                'sms_school_candidate_name' => $schoolCandidateName
            )
        );
    }

    protected function calStrlen($str)
    {
        return (strlen($str) + mb_strlen($str, 'UTF8')) / 2;
    }

    protected function setCloudSmsKey($key, $val)
    {
        $setting       = $this->getSettingService()->get('cloud_sms', array());
        $setting[$key] = $val;
        $this->getSettingService()->set('cloud_sms', $setting);
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

        if (strpos($address, '192.168.') === 0) {
            return true;
        }

        if (strpos($address, '10.') === 0) {
            return true;
        }

        return false;
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }
}
