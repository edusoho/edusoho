<?php
namespace Topxia\WebBundle\Command;

use Topxia\Service\Util\PluginUtil;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpgradeScriptCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:upgrade-script')
            ->addArgument('version', InputArgument::REQUIRED, '要升级的版本号')
            ->setDescription('用于命令行中执行指定版本的升级脚本');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $code    = 'MAIN';
        $version = $input->getArgument('version');

        $this->executeScript($code, $version);
        $output->writeln("<info>执行脚本</info>");

        $this->removeCache();
        $output->writeln("<info>删除缓存</info>");

        $this->updateApp($code, $version);
        $output->writeln("<info>元数据更新</info>");
    }

    protected function executeScript($code, $version, $index = 0)
    {
        $scriptFile = $this->getServiceKernel()->getParameter('kernel.root_dir')."/../scripts/upgrade-{$version}.php";

        if (!file_exists($scriptFile)) {
            return;
        }

        include_once $scriptFile;
        $upgrade = new \EduSohoUpgrade($this->getServiceKernel());

        if (method_exists($upgrade, 'update')) {
            $info = $upgrade->update($index);

            if (isset($info) && !empty($info['index'])) {
                $this->executeScript($code, $version, $info['index']);
            }
        }
    }

    protected function removeCache()
    {
        $cachePath  = $this->getServiceKernel()->getParameter('kernel.root_dir').'/cache/'.$this->getServiceKernel()->getEnvironment();
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
            'code'        => $code,
            'version'     => $version,
            'fromVersion' => $app['version'],
            'updatedTime' => time()
        );

        $this->getLogService()->info('system', 'update_app_version', "命令行更新应用「{$app['name']}」版本为「{$version}」");
        return $this->getAppDao()->updateApp($app['id'], $newApp);
    }

    protected function getAppDao()
    {
        return $this->getServiceKernel()->createDao('CloudPlatform.CloudAppDao');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }
}
