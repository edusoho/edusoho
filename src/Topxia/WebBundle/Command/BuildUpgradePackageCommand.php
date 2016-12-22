<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildUpgradePackageCommand extends BaseCommand
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    protected $fromVersion;
    protected $version;

    protected function configure()
    {
        $this
            ->setName('build:upgrade-package')
            ->setDescription('自动编制升级包')
            ->addArgument('fromVersion', InputArgument::REQUIRED, 'compare from  version')
            ->addArgument('version', InputArgument::REQUIRED, 'compare to  version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始编制升级包</info>');

        $fromVersion = $input->getArgument('fromVersion');
        $version     = $input->getArgument('version');

        $diffFile = 'build/diff-'.$version;

        $this->filesystem = new Filesystem();
        $this->output     = $output;
        $this->input      = $input;
        $this->fromVersion= $fromVersion;
        $this->version    = $version;

        $this->generateDiffFile();

        $submoduleDiffs = $this->generateSubmodulesDiffFile(array());

        $this->diffFilePrompt($diffFile, $submoduleDiffs);

        $packageDirectory = $this->createDirectory();

        $this->generateFiles($diffFile, $submoduleDiffs, $packageDirectory);

        $this->copyUpgradeScript($packageDirectory);

        $this->zipPackage($packageDirectory);

        $this->printChangeLog();
        $this->output->writeln('<question>编制升级包完毕</question>');
    }

    private function generateFiles($diffFile, array $submoduleDiffFiles, $packageDirectory)
    {
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '../';

        $diffFile = $rootDir . $diffFile;

        if (!$this->filesystem->exists($diffFile)) {
            $this->output->writeln("<error>差异文件 {$diffFile}, 不存在,无法制作升级包</error>");
            exit;
        }

        $moduleFiles = array_merge(array(
            '' => $diffFile
        ), $submoduleDiffFiles);

        foreach ($moduleFiles as $module => $filePath){
            $file = @fopen($filePath, "r");
            while (!feof($file)) {
                $line = fgets($file);

                $splitLine = preg_split('/\s+/', $line);

                if(empty($splitLine) || count($splitLine) === 1){
                    continue;
                }

                list($op, $opFile, $newFile) = $splitLine;
                $op = $line[0];
                if (empty($line)) {
                    continue;
                }

                if (!in_array($line[0], array('M', 'A', 'D', 'R'))) {
                    echo "无法处理该文件：{$line}";
                    continue;
                }

                if (empty($opFile)) {
                    echo "无法处理该文件：{$line}";
                    continue;
                }

                if(!empty($module)){
                    $opFile = $module . DIRECTORY_SEPARATOR . $opFile;
                    $newFile= $module . DIRECTORY_SEPARATOR . $newFile;
                }

                if (strpos($opFile, 'app/DoctrineMigrations') === 0) {
                    $this->output->writeln("<comment>忽略文件：{$opFile}</comment>");
                    continue;
                }

                if (strpos($opFile, 'migrations') === 0) {
                    $this->output->writeln("<comment>忽略文件：{$opFile}</comment>");
                    continue;
                }

                if (strpos($opFile, 'plugins') === 0 || strpos($newFile, 'plugins')) {
                    $this->output->writeln("<comment>忽略文件：{$opFile}</comment>");
                    continue;
                }

                if (strpos($opFile, 'web/install') === 0) {
                    $this->output->writeln("<comment>忽略文件：{$opFile}</comment>");
                    continue;
                }

                if (strpos($opFile, 'doc') === 0) {
                    $this->output->writeln("<comment>忽略文件：{$opFile}</comment>");
                    continue;
                }

                $opBundleFile = $this->getBundleFile($opFile);

                if ($op == 'R'){
                    $this->output->writeln("<info>文件重命名：{$opFile} -> {$newFile}</info>");
                    $this->insertDelete($opFile, $packageDirectory);
                    $this->copyFileAndDir($newFile, $packageDirectory);

                    if ($opBundleFile) {
                        $this->output->writeln("<comment>增加删除文件：[BUNDLE]        {$opBundleFile}</comment>");
                        $this->insertDelete($opBundleFile, $packageDirectory);
                    }
                }

                if ($op == 'M' || $op == 'A') {
                    $this->output->writeln("<info>增加更新文件：{$opFile}</info>");
                    $this->copyFileAndDir($opFile, $packageDirectory);

                    if ($opBundleFile) {
                        $this->output->writeln("<info>增加更新文件：[BUNDLE]        {$opBundleFile}</info>");
                        $this->copyFileAndDir($opBundleFile, $packageDirectory);
                    }
                }

                if ($op == 'D') {
                    $this->output->writeln("<comment>增加删除文件：{$opFile}</comment>");
                    $this->insertDelete($opFile, $packageDirectory);

                    if ($opBundleFile) {
                        $this->output->writeln("<comment>增加删除文件：[BUNDLE]        {$opBundleFile}</comment>");
                        $this->insertDelete($opBundleFile, $packageDirectory);
                    }
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

        if(!is_file("{$root}/{$opFile}")){
            return;
        }

        $this->filesystem->copy("{$root}/{$opFile}", $destPath, true);
    }

    private function createDirectory()
    {
        $root = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../');
        $path = "{$root}/build/EduSoho_{$this->version}/";

        if ($this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }

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

    private function copyUpgradeScript($dir)
    {
        $this->output->writeln("\n\n");
        $this->output->write("<info>拷贝升级脚本：</info>");

        $path = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../').'/scripts/upgrade-'.$this->version.'.php';

        if (!file_exists($path)) {
            $this->output->writeln("无升级脚本");
        } else {
            $targetPath = realpath($dir).'/Upgrade.php';
            $this->output->writeln($path." -> {$targetPath}");
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

    private function printChangeLog()
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
            if (strpos($line, $this->version) !== false) {
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

    private function generateDiffFile()
    {
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir').'/../';

        if (!$this->filesystem->exists($rootDir . 'build')) {
            $this->filesystem->mkdir($rootDir.'build');
        }

        $gitTag   = exec("git tag | grep v{$this->fromVersion}");
        $gitRelease = exec("git branch | grep release/{$this->version}");
        if (empty($gitTag)) {
            $this->output->writeln("标签 v{$this->fromVersion} 不存在, 无法生成差异文件");
        }
        if (empty($gitRelease)) {
            $this->output->writeln("分支 release/{$this->version} 不存在, 无法生成差异文件");
            exit();
        }

        $this->output->writeln("<info>  使用 git  diff --name-status  v{$this->fromVersion} release{$this->version} > build/diff-{$this->version} 生成差异文件：build/diff-{$this->version}</info>");

        chdir($rootDir);
        $command = "git diff --name-status v{$this->fromVersion} release/{$this->version} > build/diff-{$this->version}";
        exec($command);
    }

    private function generateSubmodulesDiffFile(array $submodules)
    {
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '../';

        $submoduleDiffs = array();

        foreach ($submodules as $submodule){
            $lastCommitHash = exec("git ls-tree v{$this->fromVersion} {$submodule} | awk '{print $3}'");
            if(empty($lastCommitHash)){
                $lastCommitHash = 'v7.3.1'; //vendor的上次单独发布的时候的tag
            }

            $currentCommitHash = exec("git ls-tree release/{$this->version} {$submodule} | awk '{print $3}'");

            $submoduleDir = $rootDir . $submodule;
            chdir($submoduleDir);
            $command = "git diff --name-status {$lastCommitHash} {$currentCommitHash} > ../build/diff-{$submodule}-{$this->version}";
            exec($command);
            $submoduleDiffs[$submodule] = $rootDir . "build/diff-{$submodule}-{$this->version}";
        }

        return $submoduleDiffs;
    }

    private function diffFilePrompt($diffFile, $submoduleDiffFiles)
    {
        $askDiffFile   = false;
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '../';
        $this->output->writeln("<info>确认build/diff-{$this->version}差异文件</info>");

        $file = @fopen($rootDir . $diffFile, "r");
        while (!feof($file)) {
            $line   = fgets($file);
            $opFile = trim(substr($line, 1));
            if (!in_array($line[0], array('M', 'A', 'D', 'R')) && !empty($opFile)) {
                echo "异常的文件更新模式：{$line}";
                $askDiffFile = true;
                continue;
            }
        }
        fclose($file);

        $question = "请手动检查build/diff-{$this->version}文件是否需要编辑,继续请输入y (y/n)";
        if ($askDiffFile && $this->input->isInteractive() && !$this->askConfirmation($question)) {
            $this->output->writeln('<error>制作升级包终止!</error>');
            exit;
        }

        $askDiffFile = false;
        foreach ($submoduleDiffFiles as $submodule => $diff){
            $file = @fopen($diff, 'r');
            while (!feof($file)) {
                $line   = fgets($file);
                $opFile = trim(substr($line, 1));
                if (!in_array($line[0], array('M', 'A', 'D', 'R')) && !empty($opFile)) {
                    echo "异常的文件更新模式：{$line}";
                    $askDiffFile = true;
                    continue;
                }
            }
            fclose($file);
            $question = "请手动检查build/diff-{$submodule}-{$this->version}文件是否需要编辑,继续请输入y (y/n)";
            if ($askDiffFile && $this->input->isInteractive() && !$this->askConfirmation($question)) {
                $this->output->writeln('<error>制作升级包终止!</error>');
                exit;
            }
            $askDiffFile = false;
        }


        $askAssetsLibs = false;
        $this->output->writeln("<info>确认web/assets/libs目录文件</info>");
        $file = @fopen($rootDir . $diffFile, "r");
        while (!feof($file)) {
            $line   = fgets($file);
            $opFile = trim(substr($line, 1));

            if (strpos($opFile, 'web/assets/libs') === 0) {
                $askAssetsLibs = true;
                $this->output->writeln("<comment>web/assets/libs文件：{$line}</comment>");
            }
        }
        $question = "web/assets/libs下的文件有修改，需要在发布版本中修改seajs-global-config.js升级版本号！修改后请输入y (y/n)";
        if ($askAssetsLibs && $this->input->isInteractive() && !$this->askConfirmation($question)) {
            $this->output->writeln('<error>制作升级包终止!</error>');
            exit;
        }
        fclose($file);


        $submoduleDiffFiles = array_merge(array('' => $rootDir . $diffFile), $submoduleDiffFiles);
        $askSqlUpgrade = false;
        foreach ($submoduleDiffFiles as $submodule => $diffFilePath){
            $file = @fopen($diffFilePath, "r");
            while (!feof($file)) {
                $line   = fgets($file);
                $opFile = trim(substr($line, 1));
                if (preg_match('/^(\w+.*\/)?migrations\/\d+.*\.php$/', $opFile, $matches) === 1) {
                    $askSqlUpgrade = true;
                    $this->output->writeln("<comment>SQL脚本：{$opFile}</comment>");
                }
            }
            $question = "请根据以上sql脚本完成 scripts/upgrade-{$this->version}.php,完成后输入y (y/n)";
            fclose($file);
        }

        if ($askSqlUpgrade && $this->input->isInteractive() && !$this->askConfirmation($question)) {
            $this->output->writeln('<error>制作升级包终止!</error>');
            exit;
        }
    }

    /**
     *
     * @param  $question
     * @return bool
     */
    protected function askConfirmation($question)
    {
        return $this->getHelper('question')->ask($this->input, $this->output, new ConfirmationQuestion($question));
    }
}
