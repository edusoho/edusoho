<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use Topxia\Service\User\AuthProvider\DiscuzAuthProvider;

class SystemController extends BaseController
{
    public function reportAction()
    {
        return $this->render('TopxiaAdminBundle:System:Report/status.html.twig', array(
            'env' => $this->getSystemStatus()
        ));
    }

    public function phpAction()
    {
        phpinfo();
        return new Response();
    }

    public function ucenterAction()
    {
        $setting = $this->getSettingService()->get('user_partner', array());

        if (!empty($setting['mode']) && $setting['mode'] == 'discuz') {
            $discuzProvider = new DiscuzAuthProvider();
            if ($discuzProvider->checkConnect()) {
                return $this->createJsonResponse(array('status' => true, 'message' => '通信成功'));
            } else {
                return $this->createJsonResponse(array('status' => false, 'message' => '通信失败'));
            }
        } else {
            return $this->createJsonResponse(array('status' => true,'message' => '未开通Ucenter'));
        }
    }

    public function emailSendCheckAction()
    {
        $user = $this->getCurrentUser();
        $site = $this->getSettingService()->get('site', array());
        $mailer  = $this->getSettingService()->get('mailer', array());

        if (!empty($mailer['enabled'])) {
            try {
                $this->sendEmail(
                    $user['email'],
                    "【{$site['name']}】系统自检邮件",
                    '系统邮件发送检测测试，请不要回复此邮件！'
                );

                return $this->createJsonResponse(array('status' => true,'message' => '邮件发送正常'));
            } catch (\Exception $e) {
                $this->getLogService()->error('user', 'email_send_check', "【系统邮件发送自检】 发送邮件失败：".$e->getMessage());
                return $this->createJsonResponse(array('status' => false,'message' => '邮件发送异常'));
            }
        } else {
            return $this->createJsonResponse(array('status' => true, 'message' => '邮件发送服务并没开通！'));
        }

    }

    public function checkDirAction()
    {
        $paths = array(
            '/' => array('depth' => '<1', 'dir' => true),
            'app' => array('depth' => '<1', 'dir' => true),
            'src' => array(),
            'plugins' => array(),
            'api' => array(),
            'vendor' => array('depth' => '<1', 'dir' => true),
            'vendor2' => array('depth' => '<1', 'dir' => true),
            'vendor_user' => array('depth' => '<1', 'dir' => true),
            'web' => array('depth' => '<1', 'dir' => true)
        );

        $errorPaths = array();

        if (PHP_OS !== 'WINNT') {
            foreach ($paths as $folder => $opts) {
                $finder = new Finder();
                if (!empty($opts['depth'])) {
                    $finder->depth($opts['depth']);
                }

                if (!empty($opts['dir'])) {
                    $finder->directories();
                }

                try {
                    $finder->in($this->container->getParameter('kernel.root_dir').'/../'.$folder);
                    foreach ($finder as $fileInfo) {
                        $relaPath = $fileInfo->getRealPath();
                        if (!(is_writable($relaPath) && is_readable($relaPath))) {
                            $errorPaths[] = $relaPath;
                        }
                    }
                } catch (\Exception $e) {
                }

            }
        }

        return $this->render('TopxiaAdminBundle:System:Report/dir-permission.html.twig', array(
            'errorPaths' => $errorPaths
        ));
    }

    protected function getSystemStatus()
    {
        $env                        = array();
        $env['os']                  = PHP_OS;
        $env['phpVersion']          = PHP_VERSION;
        $env['phpVersionOk']        = version_compare(PHP_VERSION, '5.3.0') >= 0;
        $env['user']                = getenv('USER');
        $env['pdoMysqlOk']          = extension_loaded('pdo_mysql');
        $env['uploadMaxFilesize']   = ini_get('upload_max_filesize');
        $env['uploadMaxFilesizeOk'] = intval($env['uploadMaxFilesize']) >= 2;
        $env['postMaxsize']         = ini_get('post_max_size');
        $env['postMaxsizeOk']       = intval($env['postMaxsize']) >= 8;
        $env['maxExecutionTime']    = ini_get('max_execution_time');
        $env['maxExecutionTimeOk']  = ini_get('max_execution_time') >= 30;
        $env['mbstringOk']          = extension_loaded('mbstring');
        $env['gdOk']                = extension_loaded('gd');
        $env['curlOk']              = extension_loaded('curl');
        $env['safemode'] = ini_get('safe_mode');

        return $env;
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
