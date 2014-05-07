<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\Paginator;

class SettingController extends BaseController
{
    public function siteAction(Request $request)
    {
        $site = $this->getSettingService()->get('site', array());

        $default = array(
            'name'=>'',
            'slogan'=>'',
            'url'=>'',
            'logo'=>'',
            'seo_keywords'=>'',
            'seo_description'=>'',
            'master_email'=>'',
            'icp'=>'',
            'analytics'=>'',
            'status'=>'open',
            'closed_note'=>'',
            'favicon'=>'',
            'copyright'=>''
        );

        $site = array_merge($default, $site);

        if ($request->getMethod() == 'POST') {
            $site = $request->request->all();
            $this->getSettingService()->set('site', $site);
            $this->getLogService()->info('system', 'update_settings', "更新站点设置", $site);
            $this->setFlashMessage('success', '站点信息设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:site.html.twig', array(
            'site'=>$site
        ));
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
            'url' =>  $this->container->get('templating.helper.assets')->getUrl($site['logo']),
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
            'url' =>  $this->container->get('templating.helper.assets')->getUrl($site['favicon']),
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

    public function authAction(Request $request)
    {
        $auth = $this->getSettingService()->get('auth', array());

        $default = array(
            'register_mode'=>'closed',
            'email_activation_title' => '',
            'email_activation_body' => '',
            'welcome_enabled' => 'closed',
            'welcome_sender' => '',
            'welcome_methods' => array(),
            'welcome_title' => '',
            'welcome_body' => '',
        );

        $auth = array_merge($default, $auth);

        if ($request->getMethod() == 'POST') {
            $auth = $request->request->all();
            if (empty($auth['welcome_methods'])) {
                $auth['welcome_methods'] = array();
            }
            $this->getSettingService()->set('auth', $auth);

            $this->getLogService()->info('system', 'update_settings', "更新注册设置", $auth);
            $this->setFlashMessage('success','注册设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:auth.html.twig', array(
            'auth' => $auth
        ));
    }

    public function mailerAction(Request $request)
    {
        $mailer = $this->getSettingService()->get('mailer', array());
        $default = array(
            'enabled'=>0,
            'host'=>'',
            'port'=>'',
            'username'=>'',
            'password'=>'',
            'from'=>'',
            'name'=>'',
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

    public function loginConnectAction(Request $request)
    {
        $loginConnect = $this->getSettingService()->get('login_bind', array());

        $default = array(
            'login_limit'=>0,
            'enabled'=>0,
            'weibo_enabled'=>0,
            'weibo_key'=>'',
            'weibo_secret'=>'',
            'qq_enabled'=>0,
            'qq_key'=>'',
            'qq_secret'=>'',
            'renren_enabled'=>0,
            'renren_key'=>'',
            'renren_secret'=>'',
            'verify_code' => '',
        );

        $loginConnect = array_merge($default, $loginConnect);
        if ($request->getMethod() == 'POST') {
            $loginConnect = $request->request->all();
            $this->getSettingService()->set('login_bind', $loginConnect);
            $this->getLogService()->info('system', 'update_settings', "更新登录设置", $loginConnect);
            $this->setFlashMessage('success','登录设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:login-connect.html.twig', array(
            'loginConnect' => $loginConnect
        ));
    }

    public function paymentAction(Request $request)
    {
        $payment = $this->getSettingService()->get('payment', array());
        $default = array(
            'enabled'=>0,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'=>'none',
            'alipay_enabled'=>0,
            'alipay_key'=>'',
            'alipay_secret' => '',
            'alipay_type' => 'direct',
            'tenpay_enabled'=>0,
            'tenpay_key'=>'',
            'tenpay_secret'=>''
        );

        $payment = array_merge($default, $payment);
        if ($request->getMethod() == 'POST') {
            $payment = $request->request->all();
            $this->getSettingService()->set('payment', $payment);
            $this->getLogService()->info('system', 'update_settings', "更支付方式设置", $payment);
            $this->setFlashMessage('success', '支付方式设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:payment.html.twig', array(
            'payment' => $payment,
        ));
    }

    public function refundAction(Request $request)
    {
        $refundSetting = $this->getSettingService()->get('refund', array());
        $default = array(
            'maxRefundDays' => 0,
            'applyNotification' => '',
            'successNotification' => '',
            'failedNotification' => '',
        );

        $refundSetting = array_merge($default, $refundSetting);

        if ($request->getMethod() == 'POST') {
            $refundSetting = $request->request->all();
            $this->getSettingService()->set('refund', $refundSetting);
            $this->getLogService()->info('system', 'update_settings', "更新退款设置", $refundSetting);
            $this->setFlashMessage('success', '退款设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:refund.html.twig', array(
            'refundSetting' => $refundSetting,
        ));
    }

    public function ipBlacklistAction(Request $request)
    {
        $ips = $this->getSettingService()->get('blacklist_ip', array());

        if(!empty($ips)){
            $default['ips'] =  join("\n", $ips['ips']);
            $ips = array_merge($ips, $default);
        }

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $ips['ips'] = array_filter(explode(' ', str_replace(array("\r\n", "\n", "\r")," ",$data['ips'])));
            $this->getSettingService()->set('blacklist_ip', $ips);
            $this->getLogService()->info('system', 'update_settings', "更新IP黑名单", $ips);

            $ips = $this->getSettingService()->get('blacklist_ip', array());
            $ips['ips'] =  join("\n", $ips['ips']);

            $this->setFlashMessage('success','保存成功！');
        }

        return $this->render('TopxiaAdminBundle:System:ip-blacklist.html.twig', array(
            'ips' => $ips
        ));
    }

    public function storageAction(Request $request)
    {
        $storageSetting = $this->getSettingService()->get('storage', array());

        $default = array(
            'upload_mode' => 'local',
            'cloud_access_key' => '',
            'cloud_secret_key' => '',
            'cloud_bucket' => '',
            'cloud_api_server' => '',
        );

        $storageSetting = array_merge($default, $storageSetting);
        if ($request->getMethod() == 'POST') {
            $storageSetting = $request->request->all();
            $this->getSettingService()->set('storage', $storageSetting);
            $this->getLogService()->info('system', 'update_settings', "更新云平台设置", $storageSetting);
            $this->setFlashMessage('success', '云平台设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:storage.html.twig', array(
            'storageSetting'=>$storageSetting
        ));
    }

    public function customerServiceAction(Request $request)
    {
        $customerServiceSetting = $this->getSettingService()->get('customerService', array());

        $default = array(
            'customer_service_mode' => 'closed',
            'customer_of_qq' => '',
            'customer_of_mail' => '',
            'customer_of_phone' => ''
        );

        $customerServiceSetting = array_merge($default, $customerServiceSetting);

        if ($request->getMethod() == 'POST') {
            $customerServiceSetting = $request->request->all();
            $this->getSettingService()->set('customerService', $customerServiceSetting);
            $this->getLogService()->info('system', 'customerServiceSetting', "客服管理设置", $customerServiceSetting);
            $this->setFlashMessage('success', '客服管理设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:customer-service.html.twig', array(
            'customerServiceSetting'=>$customerServiceSetting
        ));
    }

    public function userCenterAction(Request $request)
    {
        $setting = $this->getSettingService()->get('user_partner', array());

        $default = array(
            'mode' => 'default',
            'nickname_enabled' => 0,
            'avatar_alert' => 'none',
        );
        
        $setting = array_merge($default, $setting);

        $configDirectory = $this->getServiceKernel()->getParameter('kernel.root_dir') . '/config/';
        $discuzConfigPath = $configDirectory . 'uc_client_config.php';
        $phpwindConfigPath = $configDirectory . 'windid_client_config.php';

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $setting = array('mode' => $data['mode'],
                            'nickname_enabled' => $data['nickname_enabled'],
                            'avatar_alert' => $data['avatar_alert'],
            );
            $this->getSettingService()->set('user_partner', $setting);

            $discuzConfig = $data['discuz_config'];
            $phpwindConfig = $data['phpwind_config'];

            if ($setting['mode'] == 'discuz') {
                if (!file_exists($discuzConfigPath) or !is_writeable($discuzConfigPath)) {
                    $this->setFlashMessage('danger', "配置文件{$discuzConfigPath}不可写，请打开此文件，复制Ucenter配置的内容，覆盖原文件的配置。");
                    goto response;
                }
                file_put_contents($discuzConfigPath, $discuzConfig);
            } elseif ($setting['mode'] == 'phpwind') {
                if (!file_exists($phpwindConfigPath) or !is_writeable($phpwindConfigPath)) {
                    $this->setFlashMessage('danger', "配置文件{$phpwindConfigPath}不可写，请打开此文件，复制WindID配置的内容，覆盖原文件的配置。");
                    goto response;
                }
                file_put_contents($phpwindConfigPath, $phpwindConfig);
            }

            $this->getLogService()->info('system', 'setting', "用户中心设置", $setting);
            $this->setFlashMessage('success', '用户中心设置已保存！');
        }

        if (file_exists($discuzConfigPath)) {
            $discuzConfig = file_get_contents($discuzConfigPath);
        } else {
            $discuzConfig = '';
        }

        if (file_exists($phpwindConfigPath)) {
            $phpwindConfig = file_get_contents($phpwindConfigPath);
        } else {
            $phpwindConfig = '';
        }

        response:
        return $this->render('TopxiaAdminBundle:System:user-center.html.twig', array(
            'setting' => $setting,
            'discuzConfig' => $discuzConfig,
            'phpwindConfig' => $phpwindConfig,
        ));
    }

    public function courseSettingAction(Request $request)
    {
        $courseSetting = $this->getSettingService()->get('course', array());
        $default = array(
            'welcome_message_enabled' => '0',
            'welcome_message_body' => '{{nickname}},欢迎加入课程{{course}}',
            'buy_fill_userinfo' => '0',
            'teacher_modify_price' => '1',
            'live_course_enabled' => '0',
            'max_student_num' => '0',
            'max_current_num' => '50'
        );

        $courseSetting = array_merge($default, $courseSetting);

        if ($request->getMethod() == 'POST') {
            $courseSetting = $request->request->all();
            $courseSetting['max_current_num'] = '50';
            $this->getSettingService()->set('course', $courseSetting);
            $this->getLogService()->info('system', 'update_settings', "更新课程设置", $courseSetting);
            $this->setFlashMessage('success','课程设置已保存！');
        }
        
        return $this->render('TopxiaAdminBundle:System:course-setting.html.twig', array(
            'courseSetting' => $courseSetting
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

        $default = array(
            'debug' => '0',
            'app_api_url' => '',
        );

        $developerSetting = array_merge($default, $developerSetting);

        if ($request->getMethod() == 'POST') {
            $developerSetting = $request->request->all();
            $this->getSettingService()->set('developer', $developerSetting);
            $this->getLogService()->info('system', 'update_settings', "更新开发者设置", $developerSetting);
            $this->setFlashMessage('success','开发者已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:developer-setting.html.twig', array(
            'developerSetting' => $developerSetting
        ));
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

}