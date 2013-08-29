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

        // if(empty($site)){
        //     $site = array(
        //         'name'=>'',
        //         'url'=>'',
        //         'logo'=>'',
        //         'master_email'=>'',
        //         'icp'=>'',
        //         'analytics'=>'',
        //         'status'=>'closed',
        //         'closed_note'=>'',
        //         );
        // }

        $form = $this->createFormBuilder()
            ->add('name', 'text')
            ->add('slogan', 'text')
            ->add('logo', 'text')
            ->add('seo_keywords', 'text')
            ->add('seo_description', 'text')
            ->add('homepage_template', 'choice', array(
                'choices' => array(
                    'less' => '少量课程模板',
                    'more' => '大量课程模板'
                ),
            ))
            ->add('master_email', 'text')
            ->add('icp', 'text')
            ->add('status', 'choice', array(
                'expanded' => true, 
                'choices' => array('open' => '开放', 'closed' => '关闭'),
            ))
            ->add('analytics', 'textarea')
            ->add('closed_note', 'textarea')
            ->setData($site)
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $site = $request->request->all();
            $this->getSettingService()->set('site', $site);
            $this->setFlashMessage('success', '站点信息设置已保存！');
        }

        return $this->render('TopxiaAdminBundle:System:site.html.twig', array(
            'site'=>$site
        ));
    }

    public function authAction(Request $request)
    {
        $auth = $this->getSettingService()->get('auth', array());
        if(empty($auth)){
            $auth = array(
                'register_mode'=>'closed',
                'agreement'=>'',
                'email_activation_mode'=>'closed',
                'email_activation_title'=>'',
                'email_activation_body'=>'',
                'welcome_methods'=>'email',
                'welcome_title'=>'',
                'welcome_body'=>'',
                );
        }
        if ($request->getMethod() == 'POST') {
            $auth = $request->request->all();
            $this->getSettingService()->set('auth', $auth);
            $this->setFlashMessage('登录/注册设置已保存！', 'success');
        }

        return $this->render('TopxiaAdminBundle:System:auth.html.twig', array(
            'auth' => $auth
        ));
    }

    public function mailerAction(Request $request)
    {
        $mailer = $this->getSettingService()->get('mailer', array());
        if(empty($mailer)){
            $mailer = array(
                'enabled'=>0,
                'host'=>'',
                'port'=>'',
                'username'=>'',
                'password'=>'',
                'from'=>'',
                'name'=>'',
                );
        }
        if ($request->getMethod() == 'POST') {
            $mailer = $request->request->all();
            $this->getSettingService()->set('mailer', $mailer);
            $this->setFlashMessage('电子邮件设置已保存！', 'success');
        }

        return $this->render('TopxiaAdminBundle:System:mailer.html.twig', array(
            'mailer' => $mailer,
        ));
    }

    public function loginConnectAction(Request $request)
    {
        $loginConnect = $this->getSettingService()->get('login_bind', array());
        if(empty($loginConnect)){
            $loginConnect = array(
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
                'douban_enabled'=>0,
                'douban_key'=>'',
                'douban_secret'=>''
                );
        }

        if ($request->getMethod() == 'POST') {
            $loginConnect = $request->request->all();
            $this->getSettingService()->set('login_bind', $loginConnect);
            $this->setFlashMessage('第三方登录设置已保存！', 'success');
        }

        return $this->render('TopxiaAdminBundle:System:login-connect.html.twig', array(
            'loginConnect' => $loginConnect
        ));
    }

    public function paymentAction(Request $request)
    {
        $payment = $this->getSettingService()->get('payment', array());
        if(empty($payment)){
            $payment = array(
                'enabled'=>0,
                'bank_gateway'=>'',
                'alipay_enabled'=>0,
                'tenpay_enabled'=>0,
                'alipay_key'=>'',
                'tenpay_key'=>'',
                'tenpay_secret'=>''
                );
        }
        if ($request->getMethod() == 'POST') {
            $payment = $request->request->all();
            $this->getSettingService()->set('payment', $payment);
            $this->setFlashMessage('支付方式设置已保存！', 'success');
        }

        return $this->render('TopxiaAdminBundle:System:payment.html.twig', array(
            'payment' => $payment,
        ));
    }

    public function ipBlacklistAction(Request $request)
    {
        $ips = $this->getSettingService()->get('blacklist_ip', array());
        if(!empty($ips)){
            $ips['ips'] =  join("\n", $ips['ips']);
        }

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $ips['ips'] = array_filter(explode(' ', str_replace(array("\r\n", "\n", "\r")," ",$data['ips'])));
            $this->getSettingService()->set('blacklist_ip', $ips);
            $ips = $this->getSettingService()->get('blacklist_ip', array());
            $ips['ips'] =  join("\n", $ips['ips']);
            $this->setFlashMessage('保存成功！', 'success');
        }

        return $this->render('TopxiaAdminBundle:System:ip-blacklist.html.twig', array(
            'ips' => $ips
        ));
    }

    public function fileAction(Request $request)
    {
        $fileSetting = $this->getSettingService()->get('file', array());
        if(empty($fileSetting)){
            $fileSetting = array(
                'public_directory'=>'',
                'public_url'=>'',
                'private_directory'=>''
            );
        }
        
        if ($request->getMethod() == 'POST') {
            $fileSetting = $request->request->all();
            $this->getSettingService()->set('file', $fileSetting);
            $this->setFlashMessage('文件设置已保存！', 'success');
        }

        return $this->render('TopxiaAdminBundle:System:file.html.twig', array(
            'fileSetting'=>$fileSetting
        ));
    }

    public function videoAction(Request $request)
    {
        $videoSetting = $this->getSettingService()->get('video', array());

        $form = $this->createFormBuilder()
            ->add('upload_mode', 'choice', array(
                'expanded' => true, 
                'choices' => array('local' => '网站服务器', 'cloud' => '云服务器'),
            ))
            ->add('cloud_access_key', 'text')
            ->add('cloud_secret_key', 'text')
            ->add('cloud_bucket', 'text')
            ->setData($videoSetting)
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $videoSetting = $form->getData();
                $this->getSettingService()->set('video', $videoSetting);
                $this->setFlashMessage('success', '视频设置已保存！');
            }
        }

        return $this->render('TopxiaAdminBundle:System:video.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}