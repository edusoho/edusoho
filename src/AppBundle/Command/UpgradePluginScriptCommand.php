<?php

namespace AppBundle\Command;

use Biz\Util\PluginUtil;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;

class UpgradePluginScriptCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:upgrade-plugin-script')
            ->addArgument('code', InputArgument::REQUIRED, '插件code')
            ->addArgument('version', InputArgument::REQUIRED, '要升级的版本号')
            ->setDescription('用于命令行中执行插件指定版本的升级脚本');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $code = $input->getArgument('code');
        $version = $input->getArgument('version');

        $this->executeScript($code, $version);
        $output->writeln('<info>执行脚本</info>');

        PluginUtil::refresh();

        $this->updateApp($code, $version);
        $output->writeln('<info>元数据更新</info>');
    }

    protected function executeScript($code, $version)
    {
        $scriptFile = $this->getServiceKernel()->getParameter('kernel.root_dir')."/../plugins/{$code}Plugin/Scripts/UpgradeScript{$version}.php";

        if (!file_exists($scriptFile)) {
            return;
        }

        include_once $scriptFile;
        $upgradeClass = "UpgradeScript{$code}";
        $upgrade = new $upgradeClass($this->getServiceKernel()->getBiz(), $version);

        if (method_exists($upgrade, 'execute')) {
            $upgrade->execute();
        }
    }

    protected function removeCache()
    {
        $cachePath = $this->getServiceKernel()->getParameter('kernel.root_dir').'/cache/'.$this->getServiceKernel(
            )->getEnvironment();
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);
    }

    protected function updateApp($code, $version)
    {
        $app = $this->getAppService()->getAppByCode($code);

        $newApp = [
            'code' => $code,
            'version' => $version,
            'fromVersion' => $app['version'],
            'updatedTime' => time(),
        ];

        $this->getLogService()->info('system', 'update_app_version', "命令行更新应用「{$app['name']}」版本为「{$version}」");

        return $this->getAppDao()->update($app['id'], $newApp);
    }

    protected function getAppDao()
    {
        $biz = $this->getServiceKernel()->getBiz();

        return $biz->dao('CloudPlatform:CloudAppDao');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform:AppService');
    }

    protected function getLogService()
    {
        return ServiceKernel::instance()->createService('System:LogService');
    }
}
