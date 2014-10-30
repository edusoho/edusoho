<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;

class BuildPackageCommand extends BaseCommand
{
    private $fileSystem;

    protected function configure()
    {
        $this
            ->setName('topxia:build-package')
            ->setDescription('编制升级包')
            ->addArgument('name', InputArgument::REQUIRED, 'package name')
            ->addArgument('version', InputArgument::REQUIRED, 'which version to update')
            ->addArgument('diff_file', InputArgument::REQUIRED, 'Where is Diff file of both versions');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<question>开始编制升级包</question>');

        $name = $input->getArgument('name');
        $version = $input->getArgument('version');
        $diff_file = $input->getArgument('diff_file');

        $this->filesystem = new Filesystem();
        
        $packageDirectory = $this->createDirectory($name, $version);

        $this->generateFiles($diff_file, $packageDirectory, $output);

        $this->copyUpgradeScript($packageDirectory, $version, $output);

        $output->writeln('<question>编制升级包完毕</question>');
    }

    private function generateFiles($diff_file, $packageDirectory, $output)
    {
        $file = @fopen($diff_file, "r") ;  
        while (!feof($file))
        {
            $line = fgets($file);
            $op = $line[0];
            if (!in_array($line[0], array('M', 'A', 'D'))) {
                echo "无法处理该文件：{$line}";
                continue;
            }

            $opFile = trim(substr($line,1));
            if (empty($opFile)) {
                echo "无法处理该文件：{$line}";
                continue;
            }

            $opBundleFile = $this->getBundleFile($opFile);

            if ($op == 'M' or $op == 'A') {
                $output->writeln("<info>增加更新文件：{$opFile}</info>");
                $this->copyFileAndDir($opFile, $packageDirectory);
                if ($opBundleFile) {
                    $output->writeln("<info>增加更新文件：[BUNDLE]        {$opBundleFile}</info>");
                    $this->copyFileAndDir($opBundleFile, $packageDirectory);
                }
            }

            if ($op == 'D') {
                $output->writeln("<comment>增加删除文件：{$opFile}</comment>");
                $this->insertDelete($opFile, $packageDirectory);
                if ($opBundleFile) {
                    $output->writeln("<comment>增加删除文件：[BUNDLE]        {$opBundleFile}</comment>");
                    $this->insertDelete($opBundleFile, $packageDirectory);
                }

            }

        }
    }

    private function insertDelete($opFile, $packageDirectory)
    {
        file_put_contents("{$packageDirectory}/delete", "{$opFile}\n", FILE_APPEND);
    }

    private function copyFileAndDir($opFile, $packageDirectory)
    {
        $destPath = $packageDirectory . '/source/'. $opFile;
        if (!file_exists(dirname($destPath))) {
            mkdir(dirname($destPath), 0777, true);
        }

        $root = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../');

        $this->filesystem->copy("{$root}/{$opFile}", $destPath, true);
    }

    private function createDirectory($name, $version)
    {
        $root = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../');
        $path = "{$root}/build/{$name}_{$version}/";

        if ($this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }

        $this->distDirectory = $path;
        $this->filesystem->mkdir($path);

        return $path;
    }

    private function getBundleFile($file)
    {
        if (stripos($file, 'src/Topxia/WebBundle/Resources/public') === 0) {
             return str_ireplace('src/Topxia/WebBundle/Resources/public', 'web/bundles/topxiaweb', $file);
        }

        if (stripos($file, 'src/Topxia/AdminBundle/Resources/public') === 0) {
             return str_ireplace('src/Topxia/AdminBundle/Resources/public', 'web/bundles/topxiaadmin', $file);
        }

        return null;
    }

    private function copyUpgradeScript($dir, $version, $output)
    {
        $output->writeln("\n\n");
        $output->write("<info>拷贝升级脚本：</info>");

        $path = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../') . '/scripts/upgrade-' . $version . '.php';
        if (!file_exists($path)) {
            $output->writeln("无升级脚本");
        } else {
            $targetPath = realpath($dir) . '/Upgrade.php';
            $output->writeln($path . " -> {$targetPath}" );
            $this->filesystem->copy($path, $targetPath, true);
        }

    }

}