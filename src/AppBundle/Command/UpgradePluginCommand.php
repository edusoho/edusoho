<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Biz\CloudPlatform\Service\Impl\AppServiceImpl;

class UpgradePluginCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:upgrade-plugin')
            ->addArgument('code', InputArgument::REQUIRED, '插件code')
            ->addArgument('version', InputArgument::REQUIRED, '要升级的版本号')
            ->setDescription('用于命令行中，升级指定版本插件');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');
        $version = $input->getArgument('version');
        $output->writeln("<info>升级 {$code} 版本号为 {$version } </info>");
        $localAppService = new localAppServiceImpl($this->getBiz(), $code, $version);

        $localAppService->beginPackageUpdate(0, 'upgrade');

        $output->writeln('<info>升级 成功</info>');
    }

    protected function getAppService()
    {
        return $this->getBiz()->service('CloudPlatform:AppService');
    }
}

class localAppServiceImpl extends AppServiceImpl
{
    public function __construct($biz, $code, $version)
    {
        $this->biz = $biz;
        $this->code = $code;
        $this->version = $version;
    }

    public function getCenterPackageInfo($id)
    {
        return array(
            'code' => $this->code,
            'version' => $this->version,
            'fileName' => $this->code.'-'.$this->version,
            'product' => array(
                'code' => $this->code,
                'name' => $this->code,
            ),
            'fromVersion' => $this->version,
            'toVersion' => $this->version,
        );
    }

    protected function createPackageUpdateLog($package, $status = 'SUCCESS', $message = '')
    {
        print_r($message);
    }

    protected function updateAppForPackageUpdate($package, $packageDir)
    {
        return;
    }

    protected function getDownloadDirectory()
    {
        return $this->biz['root_directory'].'/build';
    }

    protected function _submitRunLogForPackageUpdate($message, $package, $errors)
    {
        return;
    }
}
