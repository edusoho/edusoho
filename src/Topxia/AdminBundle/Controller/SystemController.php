<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;

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

    public function sendEmailCheckAction()
    {
        try {
            $this->sendEmail(
                $user['email'],
                '请激活你的帐号 完成注册',
                $emailBody
            );
            $this->getLogService()->info('user', 'send_email_verify', "管理员给用户 ${user['nickname']}({$user['id']}) 发送Email验证邮件");
        } catch (\Exception $e) {
            $this->getLogService()->error('user', 'send_email_verify', "管理员给用户 ${user['nickname']}({$user['id']}) 发送Email验证邮件失败：".$e->getMessage());
            throw $e;
        }

        return $this->createJsonResponse(true);
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
}
