<?php

namespace AppBundle\Command;

use Topxia\Service\Common\ServiceKernel;
use Biz\Util\PluginUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpgradeScriptsCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:upgrade-scripts')
            ->addArgument('filePath', InputArgument::REQUIRED, '文件路径，每一行代表的是要升级的版本号')
            ->addArgument('code', InputArgument::OPTIONAL, '主程序的code，不同的产品线有不同的code，默认为：MAIN', 'MAIN')
            ->setDescription('用于命令行中执行指定版本的升级脚本');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $code = $input->getArgument('code');
        $filePath = $input->getArgument('filePath');

        $file = file($filePath);
        foreach ($file as $version) {
            $version = trim($version);
            $this->executeScript($code, $version);
            $output->writeln("<info>执行脚本{$version}</info>");

            $this->updateApp($code, $version);
            $output->writeln('<info>元数据更新</info>');
        }

        $this->removeCache();
        $output->writeln('<info>删除缓存</info>');

        $this->updateApp($code, $version);
        $output->writeln('<info>元数据更新</info>');
    }

    protected function executeScript($code, $version, $index = 0)
    {
        $scriptFile = $this->getServiceKernel()->getParameter('kernel.root_dir')."/../scripts/upgrade-{$version}.php";
        if (!file_exists($scriptFile)) {
            return;
        }

        include_once $scriptFile;
        $upgrade = new \EduSohoUpgrade($this->getServiceKernel()->getBiz());

        if (method_exists($upgrade, 'update')) {
            $info = $upgrade->update($index);
            if (isset($info) && !empty($info['index'])) {
                $this->executeScript($code, $version, $info['index']);
            }
        }
    }

    protected function removeCache()
    {
        $cachePath = $this->getServiceKernel()->getParameter('kernel.root_dir').'/cache/'.$this->getServiceKernel()->getEnvironment();
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        if (empty($errors)) {
            PluginUtil::refresh();
        }
    }

    protected function updateApp($code, $version)
    {
        $app = $this->getAppService()->getAppByCode($code);

        $newApp = array(
            'code' => $code,
            'version' => $version,
            'fromVersion' => $app['version'],
            'updatedTime' => time(),
        );

        $this->getLogService()->info('system', 'update_app_version', "命令行更新应用「{$app['name']}」版本为「{$version}」");

        return $this->getAppDao()->updateApp($app['id'], $newApp);
    }

    protected function getAppDao()
    {
        return $this->getServiceKernel()->createDao('CloudPlatform:CloudAppDao');
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
