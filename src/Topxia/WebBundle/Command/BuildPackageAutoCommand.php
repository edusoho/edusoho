<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildPackageAutoCommand extends BaseCommand
{
    private $fileSystem;

    protected $output;

    protected function configure()
    {
        $this
            ->setName('topxia:auto-build-package')
            ->setDescription('自动编制升级包')
            ->addArgument('name', InputArgument::REQUIRED, 'package name')
            ->addArgument('fromVersion', InputArgument::REQUIRED, 'compare from  version')
            ->addArgument('version', InputArgument::REQUIRED, 'compare to  version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<question>开始编制升级包</question>');

        $name        = $input->getArgument('name');
        $fromVersion = $input->getArgument('fromVersion');
        $version     = $input->getArgument('version');

        $diffFile = 'build/diff-'.$version;

        $this->filesystem = new Filesystem();
        $this->output     = $output;
        $this->input      = $input;

        $this->generateDiffFile($fromVersion, $version);

        $this->diffFilePrompt($diffFile, $version);

        $packageDirectory = $this->createDirectory($name, $version);

        $this->generateFiles($diffFile, $packageDirectory, $output);

        $this->copyUpgradeScript($packageDirectory, $version, $output);

        $this->zipPackage($packageDirectory);

        $this->printChangeLog($version);
        $output->writeln('<question>编制升级包完毕</question>');
    }

    private function generateFiles($diffFile, $packageDirectory, $output)
    {
        if (!$this->filesystem->exists($diffFile)) {
            $output->writeln("<error>差异文件 {$diffFile}, 不存在,无法制作升级包</error>");
            exit;
        }

        $file = @fopen($diffFile, "r");
        while (!feof($file)) {
            $line = fgets($file);
            $op   = $line[0];

            if (empty($line)) {
                continue;
            }

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

        if (stripos($file, 'vendor/willdurand/js-translation-bundle/Bazinga/Bundle/JsTranslationBundle/Resources/public') === 0) {
            return str_ireplace('vendor/willdurand/js-translation-bundle/Bazinga/Bundle/JsTranslationBundle/Resources/public', 'web/bundles/bazingajstranslation', $file);
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

    private function generateDiffFile($version, $toVersion)
    {
        $RootPath = $this->getContainer()->getParameter('kernel.root_dir').'/../';

        if (!$this->filesystem->exists($RootPath.'build')) {
            $this->filesystem->mkdir($RootPath.'build');
        }

        $gitTag   = exec("git tag |grep v{$version}");
        $gitRelease = exec("git branch |grep release/{$toVersion}");
        if (empty($gitTag)) {
            echo "标签 v{$version} 不存在, 无法生成差异文件\n";
        }
        if (empty($gitRelease)) {
            echo "分支 release/{$toVersion} 不存在, 无法生成差异文件\n";exit;
        }
        $this->output->writeln("<info>  使用 git  diff --name-status  v{$version} release{$toVersion} > build/diff-{$toVersion} 生成差异文件：build/diff-{$toVersion}</info>");

        chdir($RootPath);
        $command = "git  diff --name-status  v{$version} release/{$toVersion} > build/diff-{$toVersion}";
        exec($command);
    }

    private function diffFilePrompt($diffFile, $version)
    {
        $askDiffFile   = false;
        $askAssetsLibs = false;
        $askSqlUpgrade = false;

        $this->output->writeln("<info>确认build/diff-{$version}差异文件</info>");
        $file = @fopen($diffFile, "r");
        while (!feof($file)) {
            $line   = fgets($file);
            $op     = $line[0];
            $opFile = trim(substr($line, 1));
            if (!in_array($line[0], array('M', 'A', 'D')) && !empty($opFile)) {
                echo "异常的文件更新模式：{$line}";
                $askDiffFile = true;
                continue;
            }
        }
        $question = "请手动检查build/diff-{$version}文件是否需要编辑,继续请输入y (y/n)";
        if ($askDiffFile && $this->input->isInteractive() && !$this->askConfirmation($question, $this->input, $this->output)) {
            $this->output->writeln('<error>制作升级包终止!</error>');
            exit;
        }

        $this->output->writeln("<info>确认web/assets/libs目录文件</info>");
        $file = @fopen($diffFile, "r");
        while (!feof($file)) {
            $line   = fgets($file);
            $op     = $line[0];
            $opFile = trim(substr($line, 1));

            if (strpos($opFile, 'web/assets/libs') === 0) {
                $askAssetsLibs = true;
                $this->output->writeln("<comment>web/assets/libs文件：{$line}</comment>");
            }
        }
        $question = "web/assets/libs下的文件有修改，需要在发布版本中修改seajs-global-config.js升级版本号！修改后请输入y (y/n)";
        if ($askAssetsLibs && $this->input->isInteractive() && !$this->askConfirmation($question, $this->input, $this->output)) {
            $this->output->writeln('<error>制作升级包终止!</error>');
            exit;
        }

        $this->output->writeln("<info>准备制作升级脚本</info>");
        $file = @fopen($diffFile, "r");
        while (!feof($file)) {
            $line   = fgets($file);
            $op     = $line[0];
            $opFile = trim(substr($line, 1));
            if (strpos($opFile, 'app/DoctrineMigrations') === 0) {
                $askSqlUpgrade = true;
                $this->output->writeln("<comment>SQL脚本：{$opFile}</comment>");
            }
        }
        $question = "请根据以上sql脚本完成 scripts/upgrade-{$version}.php,完成后输入y (y/n)";
        if ($askSqlUpgrade && $this->input->isInteractive() && !$this->askConfirmation($question, $this->input, $this->output)) {
            $this->output->writeln('<error>制作升级包终止!</error>');
            exit;
        }
    }

    /**
     * This method ensure that we stay compatible with symfony console 2.3 by using the deprecated dialog helper
     * but use the ConfirmationQuestion when available.
     *
     * @param  $question
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return mixed
     */
    protected function askConfirmation($question, InputInterface $input, OutputInterface $output)
    {
        if ($this->getHelperSet()->has('question')) {
            return $this->getHelper('question')->ask($input, $output, new ConfirmationQuestion($question, false));
        } else {
            return $this->getHelper('dialog')->askConfirmation($output, '<question>'.$question.'</question>', false);
        }
    }
}
