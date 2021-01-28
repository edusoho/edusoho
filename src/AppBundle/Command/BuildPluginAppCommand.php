<?php

namespace AppBundle\Command;

use AppBundle\Common\BlockToolkit;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildPluginAppCommand extends BaseCommand
{
    protected $output;

    protected function configure()
    {
        $this->setName('build:plugin-app')
            ->addArgument('name', InputArgument::REQUIRED, 'plugin name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $this->output = $output;
        $this->filesystem = new Filesystem();
        $name = $input->getArgument('name');
        $this->copyTranslationsFile($name);
        $this->copyStaticFile($name);
        $this->_buildDistPackage($name);
    }

    private function copyTranslationsFile($pluginCode)
    {
        $this->output->writeln("<info>正在检测翻译文件 {$pluginCode}</info>");
        $rootDir = $this->getBiz()->offsetGet('kernel.root_dir');
        $translationsDir = $rootDir."/../plugins/{$pluginCode}Plugin/Resources/static-src/js/translations";
        $pluginPublicTranslationsDir = $rootDir."/../plugins/{$pluginCode}Plugin/Resources/public/js/controller/translations";
        $name = strtolower($pluginCode);
        $staticDir = $rootDir."/../web/static-dist/{$name}plugin/js/translations";
        if ($this->filesystem->exists($translationsDir)) {
            $this->output->writeln("<info>    *正在拷贝翻译文件 {$translationsDir} -> {$pluginPublicTranslationsDir}</info>");
            $this->filesystem->mirror($translationsDir, $pluginPublicTranslationsDir, null, array('override' => true, 'delete' => true));
            $this->output->writeln("<info>    *正在拷贝翻译文件 {$translationsDir} -> {$staticDir}</info>");
            $this->filesystem->mirror($translationsDir, $staticDir, null, array('override' => true, 'delete' => true));
        } else {
            $this->output->writeln("<warning>    *未检测到翻译文件 {$pluginCode},请查看是否执行app/console trans:dump-js --code=plugin_code</>");
        }
    }

    private function copyStaticFile($pluginCode)
    {
        $this->output->writeln("<info>正在检测静态资源文件 {$pluginCode}</info>");
        $rootDir = $this->getBiz()->offsetGet('kernel.root_dir');
        $originDir = $this->getOriginDir($rootDir, $pluginCode);
        $targetDir = $this->getTargetDir($rootDir, $pluginCode);
        if ($this->filesystem->exists($originDir)) {
            $this->output->writeln("<info>    *正在拷贝静态资源文件 {$originDir} -> {$targetDir}</info>");
            $this->filesystem->mirror($originDir, $targetDir, null, array('override' => true, 'delete' => true));
        } else {
            $this->output->writeln("<warning>    *未检测到静态资源文件 {$pluginCode}</>");
        }
    }

    private function getOriginDir($rootDir, $pluginCode)
    {
        $originDir = $rootDir.'/../web/static-dist/'.strtolower($pluginCode);
        if ('FavoriteReward' == $pluginCode) {
            $originDir = $rootDir.'/../web/static-dist/litetheme';
        }

        if (!$this->isPluginTheme($pluginCode)) {
            $originDir .= 'plugin';
        }

        return $originDir;
    }

    private function getTargetDir($rootDir, $pluginCode)
    {
        $targetDir = $rootDir.'/../plugins/'.$pluginCode.'Plugin';

        $folder = strtolower($pluginCode);
        if ('FavoriteReward' == $pluginCode) {
            $folder = 'litetheme';
        }
        if ($this->isPluginTheme($pluginCode)) {
            $targetDir .= '/theme/static-dist/'.$folder;
        } else {
            $targetDir .= '/Resources/static-dist/'.$folder.'plugin';
        }

        return $targetDir;
    }

    private function isPluginTheme($pluginCode)
    {
        $rootDir = $this->getBiz()->offsetGet('kernel.root_dir');

        return file_exists($themeDir = $rootDir.'/../plugins/'.$pluginCode.'Plugin/theme');
    }

    private function _buildDistPackage($name)
    {
        $this->output->writeln("<info>开始制作插件应用包 {$name}</info>");
        $pluginDir = $this->getPluginDirectory($name);
        $version = $this->getPluginVersion($name, $pluginDir);

        $distDir = $this->_makeDistDirectory($name, $version);
        $sourceDistDir = $this->_copySource($name, $pluginDir, $distDir);
        $this->_copyScript($name, $pluginDir, $distDir);
        $this->_generateBlocks($pluginDir, $distDir, $this->getContainer());
        $this->_copyMeta($pluginDir, $distDir);
        $this->_cleanGit($sourceDistDir);
        $this->_zipPackage($distDir);
    }

    private function _copySource($name, $pluginDir, $distDir)
    {
        $sourceTargetDir = $distDir.'/source/'.$name.'Plugin';
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

    private function _copyScript($name, $pluginDir, $distDir)
    {
        $scriptDir = "{$pluginDir}/Scripts";
        $distScriptDir = "{$distDir}/Scripts";

        if ($this->filesystem->exists($scriptDir)) {
            $this->filesystem->mirror($scriptDir, $distScriptDir);
            $this->output->writeln("<info>    * 拷贝脚本：{$scriptDir} -> {$distScriptDir}</info>");
        } else {
            $this->output->writeln('<comment>    * 拷贝脚本：无</comment>');
        }

        $this->output->writeln('<info>    * 生成安装引导脚本：EduSohoPluginUpgrade.php</info>');

        $data = file_get_contents(__DIR__.'/Fixtures/PluginAppUpgradeTemplate.php');
        $data = str_replace('{{code}}', $name, $data);
        file_put_contents("{$distDir}/EduSohoPluginUpgrade.php", $data);
    }

    private function _copyMeta($pluginDir, $distDir)
    {
        $source = "{$pluginDir}/plugin.json";
        $target = "{$distDir}/plugin.json";
        $this->filesystem->copy($source, $target);
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
        $this->output->writeln('<question>    * ZIP包大小：'.intval(filesize($zipPath) / 1024).' Kb');
    }

    private function _makeDistDirectory($name, $version)
    {
        $distDir = dirname("{$this->getContainer()->getParameter('kernel.root_dir')}")."/build/{$name}-{$version}";

        if ($this->filesystem->exists($distDir)) {
            $this->output->writeln("<info>    清理目录：{$distDir}</info>");
            $this->filesystem->remove($distDir);
        }

        $this->output->writeln("<info>    创建目录：{$distDir}</info>");
        $this->filesystem->mkdir($distDir);

        return realpath($distDir);
    }

    private function _generateBlocks($pluginDir, $distDir, $container)
    {
        if (file_exists($pluginDir.'/block.json')) {
            $this->filesystem->copy($pluginDir.'/block.json', $distDir.'/block.json');
            BlockToolkit::generateBlockContent($pluginDir.'/block.json', $distDir.'/blocks', $container);
        }
    }

    private function getPluginVersion($name, $pluginDir)
    {
        $meta = json_decode(file_get_contents($pluginDir.'/plugin.json'), true);

        if (empty($meta) || empty($meta['version'])) {
            throw new \RuntimeException('获取插件版本号失败！');
        }

        return $meta['version'];
    }

    private function getPluginDirectory($name)
    {
        $pluginDir = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../plugins/'.$name.'Plugin');

        if (empty($pluginDir)) {
            throw new \RuntimeException("${pluginDir}目录不存在");
        }

        return $pluginDir;
    }
}
