<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Component\OAuthClient\OAuthClientFactory;
use Topxia\Service\Util\LiveClientFactory;
use Topxia\Service\Util\CloudClientFactory;

class SettingController extends BaseController
{
    public function mobileAction(Request $request)
    {
        $operationMobile = $this->getSettingService()->get('operation_mobile', array());
        $courseGrids = $this->getSettingService()->get('operation_course_grids', array());
        $settingMobile = $this->getSettingService()->get('mobile', array());

        $default = array(
            'enabled' => 0, // 网校状态
            'about' => '', // 网校简介
            'logo' => '', // 网校Logo
            'notice' => '', //公告
            'splash1' => '', // 启动图1
            'splash2' => '', // 启动图2
            'splash3' => '', // 启动图3
            'splash4' => '', // 启动图4
            'splash5' => '', // 启动图5
        );

        $mobile = array_merge($default, $settingMobile);
        if ($request->getMethod() == 'POST') {
            $settingMobile = $request->request->all();
            $mobile = array_merge($settingMobile,$operationMobile,$courseGrids);

            $this->getSettingService()->set('operation_mobile', $operationMobile);
            $this->getSettingService()->set('operation_course_grids', $courseGrids);
            $this->getSettingService()->set('mobile', $mobile);


            $this->getLogService()->info('system', 'update_settings', "更新移动客户端设置", $mobile);
            $this->setFlashMessage('success', '移动客户端设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:mobile.setting.html.twig', array(
            'mobile' => $mobile,
        ));
    }

    public function mobilePictureUploadAction(Request $request, $type)
    {
        $file = $request->files->get($type);
        if (!FileToolkit::isImageFile($file)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }

        $filename = 'mobile_picture' . time() . '.' . $file->getClientOriginalExtension();
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/system";
        $file = $file->move($directory, $filename);

        $mobile = $this->getSettingService()->get('mobile', array());
        $mobile[$type] = "{$this->container->getParameter('topxia.upload.public_url_path')}/system/{$filename}";
        $mobile[$type] = ltrim($mobile[$type], '/');

        $this->getSettingService()->set('mobile', $mobile);

        $this->getLogService()->info('system', 'update_settings', "更新网校{$type}图片", array($type => $mobile[$type]));

        $response = array(
            'path' => $mobile[$type],
            'url' => $this->container->get('templating.helper.assets')->getUrl($mobile[$type]),
        );

        return new Response(json_encode($response));
    }

    public function mobilePictureRemoveAction(Request $request, $type)
    {
        $setting = $this->getSettingService()->get("mobile");
        $setting[$type] = '';

        $this->getSettingService()->set('mobile', $setting);

        $this->getLogService()->info('system', 'update_settings', "移除网校{$type}图片");

        return $this->createJsonResponse(true);
    }

    public function logoUploadAction(Request $request)
    {
        $file = $request->files->get('logo');
        if (!FileToolkit::isImageFile($file)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }

        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();

        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/system";
        $file = $file->move($directory, $filename);

        $site = $this->getSettingService()->get('site', array());

        $site['logo'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/system/{$filename}";
        $site['logo'] = ltrim($site['logo'], '/');

        $this->getSettingService()->set('site', $site);

        $this->getLogService()->info('system', 'update_settings', "更新站点LOGO", array('logo' => $site['logo']));

        $response = array(
            'path' => $site['logo'],
            'url' => $this->container->get('templating.helper.assets')->getUrl($site['logo']),
        );

        return new Response(json_encode($response));

    }

    public function logoRemoveAction(Request $request)
    {
        $setting = $this->getSettingService()->get("site");
        $setting['logo'] = '';

        $this->getSettingService()->set('site', $setting);

        $this->getLogService()->info('system', 'update_settings', "移除站点LOGO");

        return $this->createJsonResponse(true);
    }

    public function liveLogoUploadAction(Request $request)
    {
        $file = $request->files->get('logo');
        if (!FileToolkit::isImageFile($file)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }

        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();

        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/system";
        $file = $file->move($directory, $filename);

        $courseSetting = $this->getSettingService()->get('course', array());

        $courseSetting['live_logo'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/system/{$filename}";
        $courseSetting['live_logo'] = ltrim($courseSetting['live_logo'], '/');

        $this->getSettingService()->set('course', $courseSetting);

        $this->getLogService()->info('system', 'update_settings', "更新站点LOGO", array('live_logo' => $courseSetting['live_logo']));

        $response = array(
            'path' => $courseSetting['live_logo'],
            'url' => $this->container->get('templating.helper.assets')->getUrl($courseSetting['live_logo']),
        );

        return new Response(json_encode($response));

    }

    public function liveLogoRemoveAction(Request $request)
    {
        $setting = $this->getSettingService()->get("course");
        $setting['live_logo'] = '';

        $this->getSettingService()->set('course', $setting);

        $this->getLogService()->info('system', 'update_settings', "移除直播LOGO");

        return $this->createJsonResponse(true);
    }

    public function faviconUploadAction(Request $request)
    {
        $file = $request->files->get('favicon');
        if (!FileToolkit::isIcoFile($file)) {
            throw $this->createAccessDeniedException('图标格式不正确！');
        }
        $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();

        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/system";
        $file = $file->move($directory, $filename);

        $site = $this->getSettingService()->get('site', array());

        $site['favicon'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/system/{$filename}";
        $site['favicon'] = ltrim($site['favicon'], '/');

        $this->getSettingService()->set('site', $site);

        $this->getLogService()->info('system', 'update_settings', "更新浏览器图标", array('favicon' => $site['favicon']));

        $response = array(
            'path' => $site['favicon'],
            'url' => $this->container->get('templating.helper.assets')->getUrl($site['favicon']),
        );

        return new Response(json_encode($response));
    }

    public function faviconRemoveAction(Request $request)
    {
        $setting = $this->getSettingService()->get("site");
        $setting['favicon'] = '';

        $this->getSettingService()->set('site', $setting);

        $this->getLogService()->info('system', 'update_settings', "移除站点浏览器图标");

        return $this->createJsonResponse(true);
    }
    
    private function setCloudSmsKey($key, $val)
    {
        $setting = $this->getSettingService()->get('cloud_sms', array());
        $setting[$key] = $val;
        $this->getSettingService()->set('cloud_sms', $setting);
    }

    public function mailerAction(Request $request)
    {
        $mailer = $this->getSettingService()->get('mailer', array());
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
        if ($request->getMethod() == 'POST') {
            $mailer = $request->request->all();
            $this->getSettingService()->set('mailer', $mailer);
            $this->getLogService()->info('system', 'update_settings', "更新邮件服务器设置", $mailer);
            $this->setFlashMessage('success', '电子邮件设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:mailer.html.twig', array(
            'mailer' => $mailer,
        ));
    }

    public function defaultAction(Request $request)
    {
        $defaultSetting = $this->getSettingService()->get('default', array());
        $path = $this->container->getParameter('kernel.root_dir') . '/../web/assets/img/default/';

        $default = $this->getDefaultSet();

        $defaultSetting = array_merge($default, $defaultSetting);

        if ($request->getMethod() == 'POST') {
            $defaultSetting = $request->request->all();

            if (isset($defaultSetting['user_name'])) {
                $defaultSetting['user_name'] = $defaultSetting['user_name'];
            } else {
                $defaultSetting['user_name'] = '学员';
            }

            if (isset($defaultSetting['chapter_name'])) {
                $defaultSetting['chapter_name'] = $defaultSetting['chapter_name'];
            } else {
                $defaultSetting['chapter_name'] = '章';
            }

            if (isset($defaultSetting['part_name'])) {
                $defaultSetting['part_name'] = $defaultSetting['part_name'];
            } else {
                $defaultSetting['part_name'] = '节';
            }

            $default = $this->getSettingService()->get('default', array());
            $defaultSetting = array_merge($default, $defaultSetting);

            $this->getSettingService()->set('default', $defaultSetting);
            $this->getLogService()->info('system', 'update_settings', "更新系统默认设置", $defaultSetting);
            $this->setFlashMessage('success', '系统默认设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:default.html.twig', array(
            'defaultSetting' => $defaultSetting,
            'hasOwnCopyright' => false,
        ));
    }

    private function getDefaultSet()
    {
        $default = array(
            'defaultAvatar' => 0,
            'defaultCoursePicture' => 0,
            'defaultAvatarFileName' => 'avatar',
            'defaultCoursePictureFileName' => 'coursePicture',
            'articleShareContent' => '我正在看{{articletitle}}，关注{{sitename}}，分享知识，成就未来。',
            'courseShareContent' => '我正在学习{{course}}，收获巨大哦，一起来学习吧！',
            'groupShareContent' => '我在{{groupname}}小组,发表了{{threadname}},很不错哦,一起来看看吧!',
            'classroomShareContent' => '我正在学习{{classroom}}，收获巨大哦，一起来学习吧！',
            'user_name' => '学员',
            'chapter_name' => '章',
            'part_name' => '节',
        );

        return $default;
    }

    public function ipBlacklistAction(Request $request)
    {
        $ips = $this->getSettingService()->get('blacklist_ip', array());

        if (!empty($ips)) {
            $default['ips'] = join("\n", $ips['ips']);
            $ips = array_merge($ips, $default);
        }

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $ips['ips'] = array_filter(explode(' ', str_replace(array("\r\n", "\n", "\r"), " ", $data['ips'])));
            $this->getSettingService()->set('blacklist_ip', $ips);
            $this->getLogService()->info('system', 'update_settings', "更新IP黑名单", $ips);

            $ips = $this->getSettingService()->get('blacklist_ip', array());
            $ips['ips'] = join("\n", $ips['ips']);

            $this->setFlashMessage('success', '保存成功！');
        }

        return $this->render('TopxiaAdminBundle:System:ip-blacklist.html.twig', array(
            'ips' => $ips,
        ));
    }

    public function customerServiceAction(Request $request)
    {
        $customerServiceSetting = $this->getSettingService()->get('customerService', array());

        $default = array(
            'customer_service_mode' => 'closed',
            'customer_of_qq' => '',
            'customer_of_mail' => '',
            'customer_of_phone' => '',
        );

        $customerServiceSetting = array_merge($default, $customerServiceSetting);

        if ($request->getMethod() == 'POST') {
            $customerServiceSetting = $request->request->all();
            $this->getSettingService()->set('customerService', $customerServiceSetting);
            $this->getLogService()->info('system', 'customerServiceSetting', "客服管理设置", $customerServiceSetting);
            $this->setFlashMessage('success', '客服管理设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:customer-service.html.twig', array(
            'customerServiceSetting' => $customerServiceSetting,
        ));
    }

    public function adminSyncAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $setting = $this->getSettingService()->get('user_partner', array());
        if (empty($setting['mode']) or !in_array($setting['mode'], array('phpwind', 'discuz'))) {
            return $this->createMessageResponse('info', '未开启用户中心，不能同步管理员帐号！');
        }

        $bind = $this->getUserService()->getUserBindByTypeAndUserId($setting['mode'], $currentUser['id']);
        if ($bind) {
            goto response;
        } else {
            $bind = null;
        }

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $partnerUser = $this->getAuthService()->checkPartnerLoginByNickname($data['nickname'], $data['password']);
            if (empty($partnerUser)) {
                $this->setFlashMessage('danger', '用户名或密码不正确。');
                goto response;
            } else {
                $this->getUserService()->changeEmail($currentUser['id'], $partnerUser['email']);
                $this->getUserService()->changeNickname($currentUser['id'], $partnerUser['nickname']);
                $this->getUserService()->changePassword($currentUser['id'], $data['password']);
                $this->getUserService()->bindUser($setting['mode'], $partnerUser['id'], $currentUser['id'], null);
                $user = $this->getUserService()->getUser($currentUser['id']);
                $this->authenticateUser($user);

                $this->setFlashMessage('success', '管理员帐号同步成功。');

                return $this->redirect($this->generateUrl('admin_setting_user_center'));
            }
        }

        response:
        return $this->render('TopxiaAdminBundle:System:admin-sync.html.twig', array(
            'mode' => $setting['mode'],
            'bind' => $bind,
        ));
    }

    public function developerAction(Request $request)
    {
        $developerSetting = $this->getSettingService()->get('developer', array());
        $storageSetting = $this->getSettingService()->get('storage', array());

        $default = array(
            'debug' => '0',
            'app_api_url' => '',
            'cloud_api_server' => empty($storageSetting['cloud_api_server']) ? '' : $storageSetting['cloud_api_server'],
            'hls_encrypted' => '1',
        );

        $developerSetting = array_merge($default, $developerSetting);

        if ($request->getMethod() == 'POST') {
            $developerSetting = $request->request->all();
            $storageSetting['cloud_api_server'] = $developerSetting['cloud_api_server'];
            $this->getSettingService()->set('storage', $storageSetting);
            $this->getSettingService()->set('developer', $developerSetting);

            $this->getLogService()->info('system', 'update_settings', "更新开发者设置", $developerSetting);
            $this->setFlashMessage('success', '开发者已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:developer-setting.html.twig', array(
            'developerSetting' => $developerSetting,
        ));
    }

    public function modifyVersionAction(Request $request)
    {
        $fromVersion = $request->query->get('fromVersion');
        $version = $request->query->get('version');
        $code = $request->query->get('code');

        if (empty($fromVersion) || empty($version) || empty($code)) {
            exit('注意参数为:<br><br>code<br>fromVersion<br>version<br><br>全填，不能为空！');
        }

        $appCount = $this->getAppservice()->findAppCount();
        $apps = $this->getAppservice()->findApps(0, $appCount);
        $appsCodes = ArrayToolkit::column($apps, 'code');

        if (!in_array($code, $appsCodes)) {
            exit('code 填写有问题！请检查!');
        }

        $fromVersionArray['fromVersion'] = $fromVersion;
        $versionArray['version'] = $version;
        $this->getAppservice()->updateAppVersion($code, $fromVersionArray, $versionArray);

        return $this->redirect($this->generateUrl('admin_app_upgrades'));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }
}
