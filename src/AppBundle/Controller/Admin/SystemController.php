<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\StringToolkit;
use Biz\System\Service\SettingService;
use Biz\User\AuthProvider\DiscuzAuthProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;

class SystemController extends BaseController
{
    public function reportAction()
    {
        return $this->render('admin/system/report/status.html.twig', array(
            'env' => $this->getSystemStatus(),
            'systemDiskUsage' => $this->getSystemDiskUsage(),
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

        if (!empty($setting['mode']) && $setting['mode'] === 'discuz') {
            $discuzProvider = new DiscuzAuthProvider($this->getBiz());

            if ($discuzProvider->checkConnect()) {
                return $this->createJsonResponse(array('status' => true, 'message' => '通信成功'));
            }

            return $this->createJsonResponse(array('status' => false, 'message' => '通信失败'));
        } else {
            return $this->createJsonResponse(array('status' => true, 'message' => '未开通Ucenter'));
        }
    }

    public function emailSendCheckAction()
    {
        $user = $this->getUser();
        $site = $this->getSettingService()->get('site', array());
        $mailer = $this->getSettingService()->get('mailer', array());
        $cloudMail = $this->getSettingService()->get('cloud_mail_crm', array());

        try {
            if (isset($cloudMail['status']) && $cloudMail['status'] === 'enable') {
                return $this->createJsonResponse(array('status' => true, 'message' => '已经使用云邮件'));
            }
            $mailOptions = array(
                'to' => $user['email'],
                'template' => 'email_system_self_test',
                'params' => array(
                    'sitename' => $site['name'],
                ),
            );

            $mailFactory = $this->getBiz()->offsetGet('mail_factory');
            $mail = $mailFactory($mailOptions);
            $mail->send();

            return $this->createJsonResponse(array('status' => true, 'message' => '邮件发送正常'));
        } catch (\Exception $e) {
            $this->getLogService()->error('system', 'email_send_check', '【系统邮件发送自检】 发送邮件失败：'.$e->getMessage());

            return $this->createJsonResponse(array('status' => false, 'message' => '邮件发送异常'));
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
            'vendor_user' => array('depth' => '<1', 'dir' => true),
            'web' => array('depth' => '<1', 'dir' => true),
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
                    $errorPaths[] = $e->getMessage();
                }
            }
        }

        return $this->render('admin/system/report/dir-permission.html.twig', array(
            'errorPaths' => $errorPaths,
        ));
    }

    protected function getSystemStatus()
    {
        $env = array();
        $env['os'] = PHP_OS;
        $env['phpVersion'] = PHP_VERSION;
        $env['phpVersionOk'] = version_compare(PHP_VERSION, '5.5.9') >= 0;
        $env['user'] = getenv('USER');
        $env['pdoMysqlOk'] = extension_loaded('pdo_mysql');
        $env['uploadMaxFilesize'] = ini_get('upload_max_filesize');
        $env['uploadMaxFilesizeOk'] = (int) ($env['uploadMaxFilesize']) >= 2;
        $env['postMaxsize'] = ini_get('post_max_size');
        $env['postMaxsizeOk'] = (int) ($env['postMaxsize']) >= 8;
        $env['maxExecutionTime'] = ini_get('max_execution_time');
        $env['maxExecutionTimeOk'] = ini_get('max_execution_time') >= 30;
        $env['mbstringOk'] = extension_loaded('mbstring');
        $env['gdOk'] = extension_loaded('gd');
        $env['curlOk'] = extension_loaded('curl');
        $env['safemode'] = ini_get('safe_mode');

        return $env;
    }

    private function getSystemDiskUsage()
    {
        $rootDir = $this->get('kernel')->getRootDir();
        $logs = array(
            'name' => '/app/logs',
            'dir' => $rootDir.'/logs',
            'title' => '用户在站点进行操作的日志存放目录',
        );

        $webFileDir = $this->get('kernel')->getContainer()->getParameter('topxia.upload.public_directory');
        $webFiles = array(
            'name' => substr($webFileDir, strrpos($webFileDir, '/')),
            'dir' => $webFileDir,
            'title' => '用户在站点上传图片的存放目录',
        );

        $materialDir = $this->get('kernel')->getContainer()->getParameter('topxia.disk.local_directory');
        $material = array(
            'name' => substr($materialDir, strrpos($materialDir, '/')),
            'dir' => $materialDir,
            'title' => '用户教学资料库中资源的所在目录(云文件除外)',
        );

        return array_map(function ($array) {
            $name = $array['name'];
            $dir = $array['dir'];
            $total = disk_total_space($dir);
            $free = disk_free_space($dir);
            $rate = (string) number_format($free / $total, 2) * 100 .'%';

            return array(
                'name' => $name,
                'rate' => $rate,
                'free' => StringToolkit::printMem($free),
                'total' => StringToolkit::printMem($total),
                'title' => $array['title'],
            );
        }, array($logs, $webFiles, $material));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
