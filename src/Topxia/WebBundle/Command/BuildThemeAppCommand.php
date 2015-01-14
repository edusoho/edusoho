<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Topxia\System;
use ZipArchive;

class BuildThemeAppCommand extends BaseCommand
{

    protected $output;

    protected function configure()
    {
        $this->setName ( 'build:theme-app' )
            ->addArgument('name', InputArgument::REQUIRED, 'theme name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->filesystem = new Filesystem();
        $name = $input->getArgument('name');

        $this->output->writeln("<info>开始制作 主题(Theme)应用包 {$name}</info>");

        $this->_buildDistPackage($name);
    }

    private function _buildDistPackage($name)
    {
        $themeDir = $this->getThemeDirectory($name);

        $distDir = $this->_makeDistDirectory($name);
        $sourceDistDir = $this->_copySource($name, $themeDir, $distDir);
        file_put_contents($distDir . '/ThemeApp', '');
        $this->_cleanGit($sourceDistDir);
        $this->_zip($distDir);
    }

    private function _copySource($name, $themeDir, $distDir)
    {
        $sourceTargetDir = $distDir . '/source/' . $name;
        $this->output->writeln("<info>    * 拷贝代码：{$themeDir} -> {$sourceTargetDir}</info>");
        $this->filesystem->mirror($themeDir, $sourceTargetDir);

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

    private function _zip($distDir)
    {   
        $buildDir = dirname($distDir);
        $filename = basename($distDir);

        if ($this->filesystem->exists("{$buildDir}/{$filename}.zip")) {
            $this->filesystem->remove("{$buildDir}/{$filename}.zip");
        }

        $this->output->writeln("<info>    * 制作ZIP包：{$buildDir}/{$filename}.zip</info>");

        $z = new ZipArchive(); 
        $z->open("{$buildDir}/{$filename}.zip", ZIPARCHIVE::CREATE); 
        $z->addEmptyDir($filename); 
        self::folderToZip($distDir, $z, strlen("$buildDir/")); 
        $z->close(); 
    }

    private static function folderToZip($folder, &$zipFile, $exclusiveLength) { 

        $handle = opendir($folder); 
        while (false !== $f = readdir($handle)) { 
          if ($f != '.' && $f != '..') { 
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

        $distDir = dirname("{$this->getContainer()->getParameter('kernel.root_dir')}") . "/build/{$name}-{$version}";

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
            throw new \RuntimeException("主题元信息版本号不存在");
        }

        return $themeJson['version'];
    }

    private function getThemeDirectory($name)
    {
        $themeDir = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../web/themes/' . $name);

        if (empty($themeDir)) {
            throw new \RuntimeException("${themeDir}目录不存在");
        }

        return $themeDir;
    }

}