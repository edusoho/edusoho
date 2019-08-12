<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\BlockToolkit;
use ZipArchive;

class BuildThemeAppCommand extends BaseCommand
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    protected function configure()
    {
        $this->setName('build:theme-app')
            ->addArgument('name', InputArgument::REQUIRED, 'theme name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $this->output = $output;
        $this->filesystem = new Filesystem();
        $name = $input->getArgument('name');

        $this->output->writeln("<info>开始制作 主题(Theme)应用包 {$name}</info>");

        $this->_buildDistPackage($name);
    }

    private function _copyScript($name, $themeDir, $distDir)
    {
        $scriptDir = "{$themeDir}/Scripts";
        $distScriptDir = "{$distDir}/Scripts";
        if ($this->filesystem->exists($scriptDir)) {
            $this->filesystem->mirror($scriptDir, $distScriptDir);
            $this->output->writeln("<info>    * 拷贝脚本：{$scriptDir} -> {$distScriptDir}</info>");
        } else {
            $this->output->writeln('<comment>    * 拷贝脚本：无</comment>');
        }

        $this->output->writeln('<info>    * 生成安装引导脚本：EduSohoPluginUpgrade.php</info>');

        $data = file_get_contents(__DIR__.'/Fixtures/ThemeAppUpgradeTemplate.php');
        $data = str_replace('{{name}}', $name, $data);
        file_put_contents("{$distDir}/EduSohoPluginUpgrade.php", $data);
    }

    private function _generateBlocks($themeDir, $distDir, $container)
    {
        if (file_exists($themeDir.'/block.json')) {
            $this->filesystem->copy($themeDir.'/block.json', $distDir.'/block.json');
            BlockToolkit::generateBlockContent($themeDir.'/block.json', $distDir.'/blocks', $container);
        }
    }

    private function _buildDistPackage($name)
    {
        $themeDir = $this->getThemeDirectory($name);

        $distDir = $this->_makeDistDirectory($name);
        $sourceDistDir = $this->_copySource($name, $themeDir, $distDir);
        $this->_copyScript($name, $themeDir, $distDir);
        $this->_generateBlocks($themeDir, $distDir, $this->getContainer());
        $this->_copyMeta($themeDir, $distDir);
        file_put_contents($distDir.'/ThemeApp', '');
        $this->_cleanGit($sourceDistDir);
        $this->_zip($distDir);
    }

    private function _copySource($name, $themeDir, $distDir)
    {
        $sourceTargetDir = $distDir.'/source/'.$name;
        if ($this->filesystem->exists($themeDir."/../../static-dist/{$name}theme")) {
            $this->filesystem->mirror($themeDir."/../../static-dist/{$name}theme", $themeDir."/static-dist/{$name}theme");
            $this->output->writeln("<info>    * 拷贝代码：{$themeDir} -> {$sourceTargetDir}</info>");
        } else {
            $this->output->writeln('<info>    * 无静态资源文件</info>');
        }

        $this->filesystem->mirror($themeDir, $sourceTargetDir);

        $this->filesystem->remove($sourceTargetDir.'/dev');

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

    private function _copyMeta($themeDir, $distDir)
    {
        $source = "{$themeDir}/theme.json";
        $target = "{$distDir}/theme.json";
        $this->filesystem->copy($source, $target);
    }

    private function _zip($distDir)
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

    private static function folderToZip($folder, ZipArchive &$zipFile, $exclusiveLength)
    {
        $handle = opendir($folder);
        while (false !== $f = readdir($handle)) {
            if ('.' != $f && '..' != $f) {
                $filePath = "$folder/$f";

                $localPath = substr($filePath, $exclusiveLength);
                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    $zipFile->addEmptyDir($localPath);
                    self::folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
    }

    private function _makeDistDirectory($name)
    {
        $version = $this->getThemeVersion($name);

        $distDir = dirname("{$this->getContainer()->getParameter('kernel.root_dir')}")."/build/{$name}-{$version}";

        if ($this->filesystem->exists($distDir)) {
            $this->output->writeln("<info>    清理目录：{$distDir}</info>");
            $this->filesystem->remove($distDir);
        }
        $this->output->writeln("<info>    创建目录：{$distDir}</info>");
        $this->filesystem->mkdir($distDir);

        return realpath($distDir);
    }

    private function getThemeVersion($name)
    {
        $themeDir = $this->getThemeDirectory($name);

        $themeJsonFile = "{$themeDir}/theme.json";
        if (!file_exists($themeJsonFile)) {
            throw new \RuntimeException("主题元信息文件{$themeJsonFile}不存在！");
        }

        $themeJson = json_decode(file_get_contents($themeJsonFile), true);
        if (empty($themeJson)) {
            throw new \RuntimeException("解析主题元信息文件{$themeJsonFile}失败，请检查格式是否正确！");
        }

        if (empty($themeJson['version'])) {
            throw new \RuntimeException('主题元信息版本号不存在');
        }

        return $themeJson['version'];
    }

    private function getThemeDirectory($name)
    {
        $themeDir = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../web/themes/'.$name);

        if (empty($themeDir)) {
            throw new \RuntimeException("${themeDir}目录不存在");
        }

        return $themeDir;
    }
}
