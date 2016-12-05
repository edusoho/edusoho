<?php
namespace Topxia\WebBundle\Command;

use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Common\SystemInitializer;

class InitAutoOpenSaasCommand extends InitCommand
{
    private $host = '';

    private $data = array();

    protected function configure()
    {
        $this->setName('util:init-auto_open_Saas')
            ->addArgument('accessKey', InputArgument::REQUIRED, 'accessKey')
            ->addArgument('secretKey',InputArgument::REQUIRED,'secretKey')
            ->addArgument('username',InputArgument::REQUIRED,'username')
            ->addArgument('email',InputArgument::REQUIRED,'email')
            ->addArgument('password',InputArgument::REQUIRED,'password')
            ->setDescription('用于初始化edusoho,自动开发Saas服务');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger("开始建校", $output);
        $this->initServiceKernel();

        $initializer = new SystemInitializer($output);
        $initializer->init();

        $accessKey = $input->getArgument('accessKey');
        $secretKey = $input->getArgument('secretKey');
        $setting = array(
            'auth'    => array('register_mode' => 'email_or_mobile'),
            'storage' => array('upload_mode' => 'cloud',
                                'cloud_access_key' => $accessKey,
                                'cloud_secret_key' => $secretKey,
                                'cloud_key_applied' => 1
                                )
        );
        $this->initSetting($setting);
        $this->logger("网校设置授权成功", $output);

        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $email = $input->getArgument('email');
        $user = array('username'=>$username,'password'=>$password,'email'=>$email);
        $user = $this->initUser($user);
        $this->logger("网校设置用户成功", $output);

        $this->logger("网校创建成功", $output);
    }

    private function logger($message, $output)
    {
        $time       = date("Y-m-d H:i:s");
        $log        = "{$time}, {$message}";
        $loggerFile = $this->getContainer()->getParameter('kernel.root_dir').'/logs/edusoho-init.log';
        file_put_contents($loggerFile, $log.PHP_EOL, FILE_APPEND);
        $output->writeln($log);
    }







    private function initUser($user)
    {
        $registerUser = array(
            'nickname'      => $user['username'],
            'emailOrMobile' => $user['email'],
            'password'      => $user['password'],
        );
        $registerUser = $this->getAuthService()->register($registerUser);
        $this->getUserService()->changeUserRoles($registerUser['id'], array(
            'ROLE_USER',
            'ROLE_TEACHER',
            'ROLE_SUPER_ADMIN'
        ));
    }

  

    private function initSetting($data)
    {
        foreach ($data as $key => $value) {
            $originValue = $this->getSettingService()->get($key, array());
            $value       = array_merge($originValue, $value);
            $this->getSettingService()->set($key, $value);
        }
    }



    protected function initServiceKernel()
    {
        $serviceKernel = ServiceKernel::create('dev', true);
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id'        => 0,
            'nickname'  => '游客',
            'currentIp' => '127.0.0.1',
            'roles'     => array('ROLE_SUPER_ADMIN')
        ));
        $serviceKernel->setCurrentUser($currentUser);
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
