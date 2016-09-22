<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<question>开始编制升级包</question>');

        $name     = $input->getArgument('name');
        $version  = $input->getArgument('version');
        $diffFile = $input->getArgument('diff_file');

        $this->filesystem = new Filesystem();
        $this->output     = $output;
        $packageDirectory = $this->createDirectory($name, $version);

        $this->generateFiles($diffFile, $packageDirectory, $output);

        $this->copyUpgradeScript($packageDirectory, $version, $output);

        $this->zipPackage($packageDirectory);

        $this->printChangeLog($version);

        $output->writeln('<question>编制升级包完毕</question>');
    }

    private function generateFiles($diffFile, $packageDirectory, $output)
    {
        $file = @fopen($diffFile, "r");

        while (!feof($file)) {
            $line = fgets($file);
            $op   = $line[0];

            if (!in_array($line[0], array('M', 'A', 'D'))) {
                echo "无法处理该文件：{$line}";
                continue;
            }

            $opFile = trim(substr($line, 1));

            if (empty($opFile)) {
                echo "无法处理该文件：{$line}";
                continue;
            }

            if (strpos($opFile, 'app/DoctrineMigrations') === 0) {
                $output->writeln("<comment>忽略文件：{$opFile}</comment>");
                continue;
            }

            if (strpos($opFile, 'plugins') === 0) {
                $output->writeln("<comment>忽略文件：{$opFile}</comment>");
                continue;
            }

            if (strpos($opFile, 'web/install') === 0) {
                $output->writeln("<comment>忽略文件：{$opFile}</comment>");
                continue;
            }

            if (strpos($opFile, 'doc') === 0) {
                $output->writeln("<comment>忽略文件：{$opFile}</comment>");
                continue;
            }

            $opBundleFile = $this->getBundleFile($opFile);

            if ($op == 'M' || $op == 'A') {
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
        $destPath = $packageDirectory.'/source/'.$opFile;

        if (!file_exists(dirname($destPath))) {
            mkdir(dirname($destPath), 0777, true);
        }

        $root = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../');

        $this->filesystem->copy("{$root}/{$opFile}", $destPath, true);
    }

    private function createDirectory($name, $version)
    {
        $root = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../');
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

        if (stripos($file, 'src/Topxia/MobileBundleV2/Resources/public') === 0) {
            return str_ireplace('src/Topxia/MobileBundleV2/Resources/public', 'web/bundles/topxiamobilebundlev2', $file);
        }

        if (stripos($file, 'src/Classroom/ClassroomBundle/Resources/public') === 0) {
            return str_ireplace('src/Classroom/ClassroomBundle/Resources/public', 'web/bundles/classroom', $file);
        }

        if (stripos($file, 'src/SensitiveWord/SensitiveWordBundle/Resources/public') === 0) {
            return str_ireplace('src/SensitiveWord/SensitiveWordBundle/Resources/public', 'web/bundles/sensitiveword', $file);
        }

        if (stripos($file, 'src/MaterialLib/MaterialLibBundle/Resources/public') === 0) {
            return str_ireplace('src/MaterialLib/MaterialLibBundle/Resources/public', 'web/bundles/materiallib', $file);
        }

        if (stripos($file, 'src/Org/OrgBundle/Resources/public') === 0) {
            return str_ireplace('src/Org/OrgBundle/Resources/public', 'web/bundles/org', $file);
        }

        if (stripos($file, 'src/Permission/PermissionBundle/Resources/public') === 0) {
            return str_ireplace('src/Permission/PermissionBundle/Resources/public', 'web/bundles/permission', $file);
        }

        return null;
    }

    private function copyUpgradeScript($dir, $version, $output)
    {
        $output->writeln("\n\n");
        $output->write("<info>拷贝升级脚本：</info>");

        $path = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../').'/scripts/upgrade-'.$version.'.php';

        if (!file_exists($path)) {
            $output->writeln("无升级脚本");
        } else {
            $targetPath = realpath($dir).'/Upgrade.php';
            $output->writeln($path." -> {$targetPath}");
            $this->filesystem->copy($path, $targetPath, true);
        }
    }

    private function zipPackage($distDir)
    {
        $buildDir = dirname($distDir);
        $filename = basename($distDir);

        if ($this->filesystem->exists("{$buildDir}/{$filename}.zip")) {
            $this->filesystem->remove("{$buildDir}/{$filename}.zip");
        }

        $this->output->writeln("<info>使用 zip -r {$filename}.zip {$filename}/  制作ZIP包：{$buildDir}/{$filename}.zip</info>");

        chdir($buildDir);
        $command = "zip -r {$filename}.zip {$filename}/";
        exec($command);

        $zipPath = "{$buildDir}/{$filename}.zip";

        $this->output->writeln('<comment>ZIP包大小：'.$this->getContainer()->get('topxia.twig.web_extension')->fileSizeFilter(filesize($zipPath)));
    }

    private function printChangeLog($version)
    {
        $changeLogPath = $this->getContainer()->getParameter('kernel.root_dir').'/../CHANGELOG';
        if (!$this->filesystem->exists($changeLogPath)) {
            $this->output->writeln("<error>CHANGELOG文件不存在,请确认CHANGELOG文件路径</error>");
            return false;
        }

        $this->output->writeln("<info>输出changelog,请确认changelog是否正确</info>");
        $file     = @fopen($this->getContainer()->getParameter('kernel.root_dir').'/../CHANGELOG', "r");
        $print    = false;
        $askPrint = false;
        while (!feof($file)) {
            $line = trim(fgets($file));
            if (strpos($line, $version) !== false) {
                $print = true;
            }

            if ($print && empty($line)) {
                $askPrint = true;
            }

            if ($askPrint && preg_match("/\\d{4}(\\-|\\/|\\.)\\d{1,2}\\1\\d{1,2}/", "$line", $matches)) {
                $print = false;
            }
            if ($print) {
                if (empty($line)) {
                    $this->output->writeln(sprintf("<comment>$line</comment>"));
                } else {
                    $this->output->writeln(sprintf("<comment>%s<br/></comment>", $line));
                }
            }
        }
    }
}
