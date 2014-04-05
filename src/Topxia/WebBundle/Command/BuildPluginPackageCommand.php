<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Topxia\System;

class BuildPluginPackageCommand extends BaseCommand
{

    protected $output;

    protected function configure()
    {
        $this->setName ( 'topxia:build-plugin-package' )
            ->addArgument('name', InputArgument::REQUIRED, 'plugin name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $name = ucfirst($input->getArgument('name'));

        $this->output->writeln("<info>开始编制应用安装/升级包 {$name}</info>");

        $this->makeInstallDistPackage($name);
        $this->makeUpgradeDistPackage($name);
    }

    private function makeInstallDistPackage($name)
    {
        $this->output->writeln("<info>  编制应用安装包</info>");

        $filesystem = new Filesystem();
        $pluginDir = $this->getPluginDirectory($name);
        $distDir = $this->_makeDistDirectory($name, 'install');

        $sourceTargetDir = $distDir . '/source/' . $name;
        $this->output->writeln("<info>    * 拷贝代码：{$pluginDir} -> {$sourceTargetDir}</info>");
        $filesystem->mirror($pluginDir, $sourceTargetDir);

        $script = "{$pluginDir}/Scripts/install.php";
        if ($filesystem->exists($script)) {
            $this->output->writeln("<info>    * 拷贝安装脚本：{$script} -> {$distDir}/Upgrade.php</info>");
            $filesystem->copy($script, "{$distDir}/Upgrade.php");
        } else {
            $this->output->writeln("<comment>    * 拷贝安装脚本：无</comment>");
        }

        $this->output->writeln("<info>    * 移除'.git'目录：$pluginDir . /.git/</info>");
        $filesystem->remove($pluginDir . "/.git/");

        $this->_zipPackage($distDir);
    }

    private function makeUpgradeDistPackage($name)
    {
        $this->output->writeln("<info>  编制应用升级包</info>");
        $filesystem = new Filesystem();
        $pluginDir = $this->getPluginDirectory($name);
        $distDir = $this->_makeDistDirectory($name, 'upgrade');

        $sourceTargetDir = $distDir . '/source/' . $name;
        $this->output->writeln("<info>    * 拷贝代码：{$pluginDir} -> {$sourceTargetDir}</info>");
        $filesystem->mirror($pluginDir, $sourceTargetDir);

        $version = $this->getPluginVersion($name);
        $script = "{$pluginDir}/Scripts/update-{$version}.php";
        if ($filesystem->exists($script)) {
            $this->output->writeln("<info>    * 拷贝安装脚本：{$script} -> {$distDir}/Upgrade.php</info>");
            $filesystem->copy($script, "{$distDir}/Upgrade.php");
        } else {
            $this->output->writeln("<comment>    * 拷贝安装脚本：无</comment>");
        }

        $this->output->writeln("<info>    * 移除'.git'目录：{$pluginDir}/.git/</info>");
        $filesystem->remove("{$pluginDir}/.git/");

        $this->_zipPackage($distDir);
    }

    private function _zipPackage($distDir)
    {


        $buildDir = dirname($distDir);
        $filename = basename($distDir);

        $filesystem = new Filesystem();
        if ($filesystem->exists("{$buildDir}/{$filename}.zip")) {
            $filesystem->remove("{$buildDir}/{$filename}.zip");
        }

        $this->output->writeln("<info>    * 制作ZIP包：{$buildDir}/{$filename}.zip</info>");

        chdir($buildDir);
        $command = "zip -r {$filename}.zip {$filename}/";
        exec($command);

        $zipPath = "{$buildDir}/{$filename}.zip";
        $this->output->writeln("<question>    * ZIP包大小：" . intval(filesize($zipPath)/1024) . ' Kb');
    }

    private function _makeDistDirectory($name, $type)
    {
        if (!in_array($type, array('install', 'upgrade'))) {
            throw new \RuntimeException('package type error');
        }

        $version = $this->getPluginVersion($name);

        $distDir = dirname("{$this->getContainer()->getParameter('kernel.root_dir')}") . "/build/{$name}/{$name}-{$version}-{$type}";

        $filesystem = new Filesystem();

        if ($filesystem->exists($distDir)) {
            $this->output->writeln("<info>    清理目录：{$distDir}</info>");
            $filesystem->remove($distDir);
        }
        $this->output->writeln("<info>    创建目录：{$distDir}</info>");
        $filesystem->mkdir($distDir);

        return realpath($distDir);
    }

    private function getPluginVersion($name)
    {
        $metaClass = "\\{$name}\PluginSystem";
        $metaObject = new $metaClass();
        return $metaObject::VERSION;
    }

    private function getPluginDirectory($name)
    {
        $pluginDir = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../plugins/' . $name);

        if (empty($pluginDir)) {
            throw new \RuntimeException("${pluginDir}目录不存在");
        }

        return $pluginDir;
    }

}