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

        $form = $this->createFormBuilder()
            ->add('name', 'text')
            ->add('url', 'text')
            ->add('logo', 'text')
            ->add('master_email', 'text')
            ->add('icp', 'text')
            ->add('google_analytics_enabled', 'choice', array(
                'expanded' => true, 
                'choices' => array(0 => '关闭', 1 => '开启'),
            ))
            ->add('google_analytics_id', 'text')
            ->add('google_analytics_params', 'textarea')
            ->add('status', 'choice', array(
                'expanded' => true, 
                'choices' => array('open' => '开放', 'closed' => '关闭'),
            ))
            ->add('analytics', 'textarea')
            ->add('closed_note', 'textarea')
            ->setData($site)
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $site = $form->getData();
                $this->getSettingService()->set('site', $site);
                $this->setFlashMessage('站点信息设置已保存！', 'success');
            }
        }

        return $this->render('TopxiaAdminBundle:System:site.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function authAction(Request $request)
    {
        $auth = $this->getSettingService()->get('auth', array());

        $form = $this->createFormBuilder()
            ->add('register_mode', 'choice', array(
                'expanded' => true, 
                'choices' => array('opened' => '开启', 'closed' => '关闭'),
            ))
            ->add('agreement', 'textarea')
            ->add('email_activation_mode', 'choice', array(
                'expanded' => true, 
                'choices' => array('required' => '必须激活', 'optional' => '推荐激活', 'closed' => '关闭'),
            ))
            ->add('email_activation_title', 'text')
            ->add('email_activation_body', 'textarea')
            ->add('welcome_methods', 'choice', array(
                'expanded' => true,
                'multiple' => true,
                'choices' => array('message' => '站内私信', 'email' => '电子邮件'),
            ))
            ->add('welcome_title', 'text')
            ->add('welcome_body', 'textarea')
            ->setData($auth)
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $auth = $form->getData();
                $this->getSettingService()->set('auth', $auth);
                $this->setFlashMessage('登录/注册设置已保存！', 'success');
            }
        }

        return $this->render('TopxiaAdminBundle:System:auth.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function mailerAction(Request $request)
    {
        $mailer = $this->getSettingService()->get('mailer', array());

        $form = $this->createFormBuilder()
            ->add('enabled', 'choice', array(
                'expanded' => true, 
                'choices' => array(0 => '关闭', 1 => '开启'),
            ))
            ->add('host', 'text')
            ->add('port', 'text')
            ->add('username', 'text')
            ->add('password', 'text')
            ->add('from', 'text')
            ->add('name', 'text')
            ->setData($mailer)
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $mailer = $form->getData();
                $this->getSettingService()->set('mailer', $mailer);
                $this->setFlashMessage('电子邮件设置已保存！', 'success');
            }
        }

        return $this->render('TopxiaAdminBundle:System:mailer.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function loginConnectAction(Request $request)
    {
        $loginConnect = $this->getSettingService()->get('login_bind', array());

        $enabledChoices = array(0 => '关闭', 1 => '开启');

        $form = $this->createFormBuilder()
            ->add('enabled', 'choice', array('expanded' => true, 'choices' => $enabledChoices))
            ->add('weibo_enabled', 'choice', array('expanded' => true, 'choices' => $enabledChoices))
            ->add('weibo_key', 'text')
            ->add('weibo_secret', 'text')
            ->add('qq_enabled', 'choice', array('expanded' => true, 'choices' => $enabledChoices))
            ->add('qq_key', 'text')
            ->add('qq_secret', 'text')
            ->add('douban_enabled', 'choice', array('expanded' => true, 'choices' => $enabledChoices))
            ->add('douban_key', 'text')
            ->add('douban_secret', 'text')
            ->add('renren_enabled', 'choice', array('expanded' => true, 'choices' => $enabledChoices))
            ->add('renren_key', 'text')
            ->add('renren_secret', 'text')
            ->setData($loginConnect)
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $loginConnect = $form->getData();
                $this->getSettingService()->set('login_bind', $loginConnect);
                $this->setFlashMessage('第三方登录设置已保存！', 'success');
            }
        }

        return $this->render('TopxiaAdminBundle:System:login-connect.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function paymentAction(Request $request)
    {
        $payment = $this->getSettingService()->get('payment', array());

        $enabledChoices = array(0 => '关闭', 1 => '开启');

        $form = $this->createFormBuilder()
            ->add('enabled', 'choice', array('expanded' => true, 'choices' => $enabledChoices))
            ->add('bank_gateway', 'choice', array(
                'expanded' => true, 
                'choices' => array('none' => '关闭', 'alipay' => '支付宝', 'tenpay' => '财付通'),
            ))
            ->add('alipay_enabled', 'choice', array('expanded' => true, 'choices' => $enabledChoices))
            ->add('alipay_key', 'text')
            ->add('alipay_secret', 'text')
            ->add('tenpay_enabled', 'choice', array('expanded' => true, 'choices' => $enabledChoices))
            ->add('tenpay_key', 'text')
            ->add('tenpay_secret', 'text')
            ->setData($payment)
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $payment = $form->getData();
                $this->getSettingService()->set('payment', $payment);
                $this->setFlashMessage('支付方式设置已保存！', 'success');
            }
        }

        return $this->render('TopxiaAdminBundle:System:payment.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function ipBlacklistAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('ips', 'textarea')
            ->setData(array('ips' =>  join("\n", $this->getSettingService()->get('blacklist_ip', array()))))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $ips = array_filter(explode(' ', str_replace(array("\r\n", "\n", "\r")," ",$data['ips'])));
                $this->getSettingService()->set('blacklist_ip', $ips);
                $this->setFlashMessage('保存成功！', 'success');
            }
        }

        return $this->render('TopxiaAdminBundle:System:ip-blacklist.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function fileAction(Request $request)
    {
        $fileSetting = $this->getSettingService()->get('file', array());

        $form = $this->createFormBuilder()
            ->add('public_directory', 'text')
            ->add('public_url', 'text')
            ->add('private_directory', 'text')
            ->setData($fileSetting)
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $fileSetting = $form->getData();
                $this->getSettingService()->set('file', $fileSetting);
                $this->setFlashMessage('文件设置已保存！', 'success');
            }
        }

        return $this->render('TopxiaAdminBundle:System:file.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function logsAction(Request $request)
    {
        $searchForm = $this->createLogSearchForm();
        $searchForm->bind($request);
        $conditions = $searchForm->getData();  

        $paginator = new Paginator(
            $this->get('request'),
            $this->getLogService()->searchLogCount($conditions),
            30
        );

        $this->getLogService()->error("Setting", "logs", "查询日志");

        $logs = $this->getLogService()->searchLogs(
            $conditions, 
            array('createdTime'=>'DESC'), 
            $paginator->getOffsetCount(), 
            $paginator->getPerPageCount()
        );
        
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($logs, 'userId'));

        return $this->render('TopxiaAdminBundle:System:logs.html.twig', array(
            'logs' => $logs,
            'paginator' => $paginator,
            'form' => $searchForm->createView(),
            'users' => $users
        ));
    }

    protected function createLogSearchForm() {
        $form = $this->createFormBuilder()
                ->add('startDateTime', 'text',array(
                    'required' => false
                ))
                ->add('endDateTime', 'text', array(
                    'required' => false
                ))
                ->add('level', 'choice', array(
                    'choices'   => array(
                        '' => '日志等级',
                        'info' => '提示', 
                        'warning' => '警告', 
                        'error' => '错误'
                    ),
                    'required'  => false,
                ))
                ->add('nickname', 'text', array(
                    'required' => false
                ))
                ->getForm();

        return $form;
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');        
    }
}