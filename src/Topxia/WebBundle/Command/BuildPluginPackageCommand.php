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
        $this->setName ( 'topxia:build-plugin' )
            ->addArgument('name', InputArgument::REQUIRED, 'plugin name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->filesystem = new Filesystem();
        $name = ucfirst($input->getArgument('name'));

        $this->output->writeln("<info>开始编制应用安装/升级包 {$name}</info>");

        $this->_buildDistPackage($name, 'install');
        $this->_buildDistPackage($name, 'upgrade');
    }

    private function _buildDistPackage($name, $type)
    {
        if (!in_array($type, array('install', 'upgrade'))) {
            throw new \RuntimeException('package type error');
        }

        $typeNames = array('install' => '安装包', 'upgrade' => '升级包');
        $this->output->writeln("<info>  编制应用{$typeNames[$type]}</info>");

        $pluginDir = $this->getPluginDirectory($name);
        $version = $this->getPluginVersion($name);

        $distDir = $this->_makeDistDirectory($name, $type);
        $sourceDistDir = $this->_copySource($name, $pluginDir, $distDir);
        $this->_copyScript($pluginDir, $distDir, $type, $version);
        $this->_cleanGit($sourceDistDir);
        $this->_zipPackage($distDir);
    }

    private function _copySource($name, $pluginDir, $distDir)
    {
        $sourceTargetDir = $distDir . '/source/' . $name;
        $this->output->writeln("<info>    * 拷贝代码：{$pluginDir} -> {$sourceTargetDir}</info>");
        $this->filesystem->mirror($pluginDir, $sourceTargetDir);

        if ($this->filesystem->exists("{$sourceTargetDir}/Scripts")) {
            $this->filesystem->remove("{$sourceTargetDir}/Scripts");
        }

        return $sourceTargetDir;
    }

    private function _cleanGit($sourceDistDir)
    {
        if (is_dir("{$sourceDistDir}/.git/")) {
            $this->output->writeln("<info>    * 移除'.git'目录：{$sourceDistDir}/.git/</info>");
            $this->filesystem->remove("{$sourceDistDir}/.git/"); 
        } else {
            $this->output->writeln("<comment>    * 移除'.git'目录： 无");
        }
    }

    private function _copyScript($pluginDir, $distDir, $type, $version)
    {
        $scriptNames = array('install' => 'install.php', 'upgrade' => "upgrade-{$version}.php");
        $script = "{$pluginDir}/Scripts/$scriptNames[$type]";
        if ($this->filesystem->exists($script)) {
            $this->output->writeln("<info>    * 拷贝脚本：{$script} -> {$distDir}/Upgrade.php</info>");
            $this->filesystem->copy($script, "{$distDir}/Upgrade.php");
        } else {
            $this->output->writeln("<comment>    * 拷贝脚本：无</comment>");
        }
    }

    private function _zipPackage($distDir)
    {
        $buildDir = dirname($distDir);
        $filename = basename($distDir);

        if ($this->filesystem->exists("{$buildDir}/{$filename}.zip")) {
            $this->filesystem->remove("{$buildDir}/{$filename}.zip");
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
        $version = $this->getPluginVersion($name);

        $distDir = dirname("{$this->getContainer()->getParameter('kernel.root_dir')}") . "/build/{$name}/{$name}-{$version}-{$type}";

        if ($this->filesystem->exists($distDir)) {
            $this->output->writeln("<info>    清理目录：{$distDir}</info>");
            $this->filesystem->remove($distDir);
        }
        $this->output->writeln("<info>    创建目录：{$distDir}</info>");
        $this->filesystem->mkdir($distDir);

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