<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\AssetsInstallCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Common\SystemInitializer;

class InitCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('system:init');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始初始化系统</info>');

        $this->installAssets($output);
        $this->initServiceKernel();
        $initializer = new SystemInitializer($output);
        $fields = array(
            'email' => 'test@edusoho.com',
            'nickname' => '测试管理员',
            'password' => 'kaifazhe',
            'createdIp' => '127.0.0.1',
        );

        $user = $initializer->initAdminUser($fields);
        $initializer->init();
        $initializer->initFolders();
        $initializer->initLockFile();
        $initializer->initRegisterSetting($user);

        // $this->installPhpCsFixerHook($output);

        $output->writeln('<info>初始化系统完毕</info>');
    }

    private function installAssets($output)
    {
        $command = new AssetsInstallCommand();
        $command->setContainer($this->getContainer());
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
}
