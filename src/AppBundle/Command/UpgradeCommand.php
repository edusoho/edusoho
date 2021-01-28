<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use Biz\Util\PluginUtil;

class UpgradeCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:upgrade')
            ->addArgument('code', InputArgument::REQUIRED, '主程序code')
            ->addArgument('version', InputArgument::REQUIRED, '要升级的版本号')
            ->setDescription('用于命令行中执行指定版本的升级包');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');
        $version = $input->getArgument('version');

        $this->initServiceKernel();

        $app = $this->getAppService()->getAppByCode($code);
        if (empty($app)) {
            $output->writeln("<info>{$code}不存在</info>");

            return;
        }

        $this->unzipUpgradePackage($code, $version);
        $output->writeln('<info>解压升级包</info>');

        $this->deleteFiles($code, $version);
        $output->writeln('<info>删除无用的文件</info>');

        $this->copyUpgradeSource($code, $version);
        $output->writeln('<info>代码复制</info>');

        $this->removeCache();
        $output->writeln('<info>删除缓存</info>');

        $this->executeScript($code, $version);
        $output->writeln('<info>执行脚本</info>');

        $this->updateApp($code, $version);
        $output->writeln('<info>元数据更新</info>');
    }

    protected function deleteFiles($code, $version)
    {
        $packageDir = $this->getServiceKernel()->getParameter('kernel.root_dir')."/data/upgrade/{$code}/{$code}_install_{$version}.zip";
        if (!file_exists($packageDir.'/delete')) {
            return;
        }

        $filesystem = new Filesystem();
        $fh = fopen($packageDir.'/delete', 'r');
        while ($filepath = fgets($fh)) {
            $fullpath = $this->getPackageRootDirectory($code, $packageDir).'/'.trim($filepath);
            if (file_exists($fullpath)) {
                $filesystem->remove($fullpath);
            }
        }
        fclose($fh);
    }

    protected function unzipUpgradePackage($code, $version)
    {
        $filepath = $this->getServiceKernel()->getParameter('kernel.root_dir')."/data/packages/{$code}/{$code}_install_{$version}.zip";
        $unzipDir = $this->getServiceKernel()->getParameter('kernel.root_dir')."/data/upgrade/{$code}/{$code}_install_{$version}.zip";

        $filesystem = new Filesystem();
        if ($filesystem->exists($unzipDir)) {
            $filesystem->remove($unzipDir);
        }

        $tmpUnzipDir = $unzipDir.'_tmp';
        if ($filesystem->exists($tmpUnzipDir)) {
            $filesystem->remove($tmpUnzipDir);
        }
        $filesystem->mkdir($tmpUnzipDir);

        $zip = new \ZipArchive();
        if ($zip->open($filepath) === true) {
            $tmpUnzipFullDir = $tmpUnzipDir.'/'.$zip->getNameIndex(0);
            $zip->extractTo($tmpUnzipDir);
            $zip->close();
            $filesystem->rename($tmpUnzipFullDir, $unzipDir);
            $filesystem->remove($tmpUnzipDir);
        } else {
            throw new \Exception('无法解压缩安装包！');
        }
    }

    protected function copyUpgradeSource($code, $version)
    {
        $packageDir = $this->getServiceKernel()->getParameter('kernel.root_dir')."/data/upgrade/{$code}/{$code}_install_{$version}.zip";
        $filesystem = new Filesystem();
        $filesystem->mirror("{$packageDir}/source", $this->getPackageRootDirectory($code, $packageDir), null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));
    }

    protected function getPackageRootDirectory($code, $packageDir)
    {
        if (in_array($code, array('MAIN', 'MOOC'))) {
            return $this->getSystemRootDirectory();
        }

        if (file_exists($packageDir.'/ThemeApp')) {
            return realpath($this->getServiceKernel()->getParameter('kernel.root_dir').'/../'.'web/themes');
        }

        return realpath($this->getServiceKernel()->getParameter('kernel.root_dir').'/../'.'plugins');
    }

    protected function getSystemRootDirectory()
    {
        return dirname($this->getServiceKernel()->getParameter('kernel.root_dir'));
    }

    protected function executeScript($code, $version)
    {
        $packageDir = $this->getServiceKernel()->getParameter('kernel.root_dir')."/data/upgrade/{$code}/{$code}_install_{$version}.zip";
        if (!file_exists($packageDir.'/Upgrade.php')) {
            return;
        }

        include_once $packageDir.'/Upgrade.php';
        $upgrade = new \EduSohoUpgrade($this->getBiz());
        $upgrade->setUpgradeType('install');

        if (method_exists($upgrade, 'update')) {
            $info = $upgrade->update();
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

        return $this->getAppDao()->update($app['id'], $newApp);
    }

    protected function getAppDao()
    {
        return $this->getBiz()->dao('CloudPlatform:CloudAppDao');
    }

    protected function getAppService()
    {
        return $this->getBiz()->service('CloudPlatform:AppService');
    }

    protected function getLogService()
    {
        return ServiceKernel::instance()->createService('System:LogService');
    }
}
