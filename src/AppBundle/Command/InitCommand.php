<?php

namespace AppBundle\Command;

use AppBundle\Common\SystemInitializer;
use Biz\System\Service\SettingService;
use Symfony\Bundle\FrameworkBundle\Command\AssetsInstallCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('system:init')
             ->addArgument('nickname', InputArgument::OPTIONAL, '初始账号的nickname')
             ->addArgument('password', InputArgument::OPTIONAL, '初始账号的password')
             ->addArgument('email', InputArgument::OPTIONAL, '初始账号的email')
             ->addArgument('accessKey', InputArgument::OPTIONAL, '站点AccessKey')
             ->addArgument('secretKey', InputArgument::OPTIONAL, '站点SecretKey');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始初始化系统</info>');

        $this->installAssets($output);
        $this->initServiceKernel();
        $initializer = new SystemInitializer($output);
        $adminUser = $this->makeAdminUser($input);

        $user = $initializer->initAdminUser($adminUser);
        $initializer->init();
        $initializer->initFolders();
        $initializer->initLockFile();
        $initializer->initRegisterSetting($user);

        $this->updateAccessKeyAndSecretKey($input);
        // $this->installPhpCsFixerHook($output);

        $output->writeln('<info>初始化系统完毕</info>');
    }

    protected function makeAdminUser(InputInterface $input)
    {
        $nickname = $input->getArgument('nickname');
        $password = $input->getArgument('password');
        $email = $input->getArgument('email');

        if (!empty($nickname) && !empty($password) && !empty($email)) {
            $adminUser = [
                'email' => $email,
                'nickname' => $nickname,
                'password' => $password,
            ];
        } else {
            $adminUser = [
                'email' => 'test@edusoho.com',
                'nickname' => '测试管理员',
                'password' => 'kaifazhe',
            ];
        }

        $adminUser['createdIp'] = '127.0.0.1';

        return $adminUser;
    }

    protected function updateAccessKeyAndSecretKey(InputInterface $input)
    {
        $accessKey = $input->getArgument('accessKey');
        $secretKey = $input->getArgument('secretKey');

        if (!empty($accessKey) && !empty($secretKey)) {
            $storage = $this->getSettingService()->get('storage');
            $storage = array_merge($storage, [
                'cloud_access_key' => $accessKey,
                'cloud_secret_key' => $secretKey,
            ]);
            $this->getSettingService()->set('storage', $storage);
        }
    }

    private function installAssets($output)
    {
        global $kernel;
        $command = new AssetsInstallCommand();
        $application = new Application($kernel);
        $command->setApplication($application);
        $subInput = new StringInput('--symlink --relative');
        $command->run($subInput, $output);
        $output->writeln('<info>installAssets成功</info>');
    }

    private function installPhpCsFixerHook($output)
    {
        $biz = $this->getBiz();
        $sourcePath = realpath($biz['root_directory']).'/.pre-push';
        $distPath = realpath($biz['root_directory']).'/.git/hooks/pre-push';

        if (!file_exists($distPath)) {
            copy($sourcePath, $distPath);
            chmod($distPath, 0755);
            $output->writeln('初始化代码格式化Hook...<info>成功</info>');
        }
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
