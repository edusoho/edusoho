<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
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
            'homepage_template'=>'less'
        );

        $site = array_merge($default, $site);

        if ($request->getMethod() == 'POST') {
            $site = $request->request->all();
            $this->getSettingService()->set('site', $site);
            $this->setFlashMessage('success', '站点信息设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:site.html.twig', array(
            'site'=>$site
        ));
    }

    public function logoUploadAction(Request $request)
    {
        $file = $request->files->get('logo');

        $filename = 'logo_' . time() . '.' . $file->guessExtension();
        
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/system";
        $file = $file->move($directory, $filename);


        $site = $this->getSettingService()->get('site', array());

        $site['logo'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/system/{$filename}";

        $this->getSettingService()->set('site', $site);

        return $this->createJsonResponse(array(
            'path' => $site['logo'],
            'url' =>  $this->container->get('templating.helper.assets')->getUrl($site['logo']),
        ));
    }

    public function logoRemoveAction(Request $request)
    {
        $setting = $this->getSettingService()->get("site");
        $setting['logo'] = '';

        $this->getSettingService()->set('site', $setting);

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
        );

        $loginConnect = array_merge($default, $loginConnect);
        if ($request->getMethod() == 'POST') {
            $loginConnect = $request->request->all();
            $this->getSettingService()->set('login_bind', $loginConnect);
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
            'bank_gateway'=>'none',
            'alipay_enabled'=>0,
            'alipay_key'=>'',
            'alipay_secret' => '',
            'tenpay_enabled'=>0,
            'tenpay_key'=>'',
            'tenpay_secret'=>''
            );
        $payment = array_merge($default, $payment);
        if ($request->getMethod() == 'POST') {
            $payment = $request->request->all();
            $this->getSettingService()->set('payment', $payment);
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
            'upload_mode'=>'local',
            'cloud_access_key'=>'',
            'cloud_secret_key'=>'',
            'cloud_bucket'=>'',
        );

        $storageSetting = array_merge($default, $storageSetting);
        if ($request->getMethod() == 'POST') {
            $storageSetting = $request->request->all();
            $this->getSettingService()->set('storage', $storageSetting);
            $this->setFlashMessage('success', '云存储设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:storage.html.twig', array(
            'storageSetting'=>$storageSetting
        ));
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}