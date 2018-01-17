<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
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
    /**
     * @var SymfonyStyle
     */
    protected $style;

    protected $packageDir;
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
        $version = $input->getArgument('version');

        $diffFile = 'build/diff-'.$version;

        $this->filesystem = new Filesystem();
        $this->output = $output;
        $this->input = $input;
        $this->style = new SymfonyStyle($input, $output);
        $this->fromVersion = $fromVersion;
        $this->version = $version;
        $this->packageDir = $this->createDirectory();

        $this->generateDiffFile();

        $this->generateFiles($diffFile);

        $this->copyUpgradeScript();

        $this->buildVendor();

        $this->zipPackage();

        $this->printChangeLog();

        $this->style->success('  编制升级包完毕');
    }

    private function generateFiles($diffFile)
    {
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'../';

        $diffFile = $rootDir.$diffFile;

        if (!$this->filesystem->exists($diffFile)) {
            $this->output->writeln("<error>差异文件 {$diffFile}, 不存在,无法制作升级包</error>");
            exit;
        }
        $fileLines = $this->processFiles($diffFile);

        $this->migrationFiles($fileLines);
        $this->webAssetsFiles($fileLines);
        $this->invalidFiles($fileLines);
        $this->ingoredFiles($fileLines);
        $this->renamedFiles($fileLines);
        $this->addedFiles($fileLines);
        $this->updatedFiles($fileLines);
        $this->deletedFiles($fileLines);
    }

    private function insertDelete($opFile, $packageDirectory)
    {
        file_put_contents("{$packageDirectory}/delete", "{$opFile}\n", FILE_APPEND);
    }

    private function copyFileAndDir($opFile, $packageDirectory)
    {
        $distPath = $packageDirectory.'/source/'.$opFile;

        if (@mkdir(dirname($distPath), 0777, true) && !is_dir(dirname($distPath))) {
            $this->output->writeln("创建升级包目录{$distPath} 失败");
            exit(1);
        }

        $root = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../');

        if (!is_file("{$root}/{$opFile}")) {
            return;
        }

        $this->filesystem->copy("{$root}/{$opFile}", $distPath, true);
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

        return null;
    }

    private function copyUpgradeScript()
    {
        $this->output->writeln('<info>  拷贝升级脚本：</info>');

        $path = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../').'/scripts/upgrade-'.$this->version.'.php';
        $upgradesDir = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../')."/scripts/{$this->version}";

        if (!file_exists($path)) {
            $this->output->writeln('无升级脚本');
        } else {
            $targetPath = realpath($this->packageDir).'/Upgrade.php';
            $this->output->writeln("    - $path .  -> {$targetPath}");
            $this->filesystem->copy($path, $targetPath, true);

            if (is_dir($upgradesDir)) {
                $this->filesystem->mirror($upgradesDir, realpath($this->packageDir).'/source/scripts/'.$this->version, null, array(
                    'override' => true,
                    'copy_on_windows' => true,
                ));
            }
        }
    }

    private function zipPackage()
    {
        $buildDir = dirname($this->packageDir);
        $filename = basename($this->packageDir);

        if ($this->filesystem->exists("{$buildDir}/{$filename}.zip")) {
            $this->filesystem->remove("{$buildDir}/{$filename}.zip");
        }

        $this->output->writeln("<info>  使用 zip -r {$filename}.zip {$filename}/  制作ZIP包：{$buildDir}/{$filename}.zip</info>");

        chdir($buildDir);
        $command = "zip -r {$filename}.zip {$filename}/";
        exec($command);

        $zipPath = "{$buildDir}/{$filename}.zip";

        $this->output->writeln('    - ZIP包大小：'.$this->getContainer()->get('web.twig.extension')->fileSizeFilter(filesize($zipPath)));
    }

    private function printChangeLog()
    {
        $changeLogPath = $this->getContainer()->getParameter('kernel.root_dir').'/../CHANGELOG';
        if (!$this->filesystem->exists($changeLogPath)) {
            $this->output->writeln('<error> CHANGELOG文件不存在,请确认CHANGELOG文件路径</error>');

            return false;
        }

        $this->output->writeln('<info>  输出changelog,请确认changelog是否正确</info>');
        $file = @fopen($this->getContainer()->getParameter('kernel.root_dir').'/../CHANGELOG', 'r');
        $print = false;
        $askPrint = false;
        while (!feof($file)) {
            $line = trim(fgets($file));
            if (strpos($line, $this->version) !== false) {
                $print = true;
            }

            if ($print && empty($line)) {
                $askPrint = true;
            }

            if ($askPrint && preg_match('/\\d{4}(\\-|\\/|\\.)\\d{1,2}\\1\\d{1,2}/', "$line", $matches)) {
                $print = false;
            }
            if ($print) {
                if (empty($line)) {
                    $this->output->writeln(sprintf("<comment>   $line</comment>"));
                } else {
                    $this->output->writeln(sprintf('<comment>   %s<br/></comment>', $line));
                }
            }
        }
    }

    private function generateDiffFile()
    {
        $this->output->writeln('<info>  生成git 差异文件 </info>  ');
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir').'/../';

        if (!$this->filesystem->exists($rootDir.'build')) {
            $this->filesystem->mkdir($rootDir.'build');
        }

        $gitTag = exec("git tag | grep v{$this->fromVersion}");
        $gitRelease = exec("git branch | grep release/{$this->version}");
        if (empty($gitTag)) {
            $this->output->writeln("标签 v{$this->fromVersion} 不存在, 无法生成差异文件");
            exit(1);
        }

        if (empty($gitRelease)) {
            $this->output->writeln("分支 release/{$this->version} 不存在, 无法生成差异文件");
            exit(1);
        }

        $this->output->writeln("    - 使用 git  diff --name-status  v{$this->fromVersion} release{$this->version} > build/diff-{$this->version} 生成差异文件：build/diff-{$this->version}");

        chdir($rootDir);
        $command = "git diff --name-status v{$this->fromVersion} release/{$this->version} > build/diff-{$this->version}";
        exec($command);
    }

    /**
     * @param  $question
     *
     * @return bool
     */
    protected function askConfirmation($question)
    {
        return $this->getHelper('question')->ask($this->input, $this->output, new ConfirmationQuestion($question));
    }

    protected function buildVendor()
    {
        $this->output->writeln('<info>  清理vendor：</info>');
        $dir = $this->packageDir.'source/vendor/';

        $command = $this->getApplication()->find('build:vendor');
        $input = new ArrayInput(array(
            'command' => 'build:vendor',
            'folder' => $dir,
        ));
        $command->run($input, $this->output);
    }

    /**
     * @param $diffFile
     *
     * @return array
     */
    private function processFiles($diffFile)
    {
        $fileLines = array();
        $handle = fopen($diffFile, 'r');
        while (($line = fgets($handle)) !== false) {
            array_push($fileLines, $line);
        }
        fclose($handle);

        return $fileLines;
    }

    private function invalidFiles($fileLines)
    {
        $this->output->writeln('<comment>  本次升级需要注意一下文件</comment>');
        $invalidFiles = array();
        foreach ($fileLines as $key => $line) {
            list($op, $opFile, $newFile) = $this->parseFileLine($line);

            if (empty($opFile) || !in_array($op, array('M', 'A', 'D', 'R'))) {
                array_push($invalidFiles, $op.' '.$opFile.' -> '.$newFile);
                continue;
            }
        }

        $this->printLog($invalidFiles, 'invalid');
    }

    private function ingoredFiles($fileLines)
    {
        $this->output->writeln('<info>  本次升级将忽略以下文件</info>');
        $ignoreFiles = array();
        foreach ($fileLines as $line) {
            list($op, $opFile, $newFile) = $this->parseFileLine($line);

            if (strpos($opFile, 'app/DoctrineMigrations') === 0) {
                array_push($ignoreFiles, $opFile);
                continue;
            }

            if (strpos($opFile, 'tests') === 0 || strpos($newFile, 'tests') === 0) {
                array_push($ignoreFiles, $opFile);
                continue;
            }

            if (strpos($opFile, 'migrations') === 0) {
                array_push($ignoreFiles, $opFile);
                continue;
            }

            if (strpos($opFile, 'plugins') === 0 || strpos($newFile, 'plugins')) {
                array_push($ignoreFiles, $opFile);
                continue;
            }

            if (strpos($opFile, 'web/install') === 0) {
                array_push($ignoreFiles, $opFile);
                continue;
            }

            if (strpos($opFile, 'doc') === 0) {
                array_push($ignoreFiles, $opFile);
                continue;
            }
            //注意，为了本地视频播放问题，忽略该文件，如果有版本改动，还是要先第一次修复，之后再改动
            if ($opFile === 'vendor/symfony/symfony/src/Symfony/Component/HttpFoundation/BinaryFileResponse.php') {
                array_push($ignoreFiles, $opFile);
                continue;
            }
        }

        $this->printLog($ignoreFiles, 'ignore');
    }

    private function renamedFiles($fileLines)
    {
        $this->output->writeln('<info>  本次升级将重命名以下文件</info>');
        $renamedFiles = array();
        foreach ($fileLines as $line) {
            list($op, $opFile, $newFile) = $this->parseFileLine($line);

            if (strpos($op, 'R') === 0) {
                $this->insertDelete($opFile, $this->packageDir);
                $this->copyFileAndDir($newFile, $this->packageDir);
                array_push($renamedFiles, "{$op}  : {$opFile} -> {$newFile}");
            }
        }

        $this->printLog($renamedFiles, 'rename');
    }

    private function addedFiles($fileLines)
    {
        $this->output->writeln('<info>  本次升级将增加以下文件</info>');
        $addedFiles = array();
        foreach ($fileLines as $line) {
            list($op, $opFile, $newFile) = $this->parseFileLine($line);

            $opBundleFile = $this->getBundleFile($opFile);
            $newBundleFile = $this->getBundleFile($newFile);

            if ($op == 'A') {
                $this->copyFileAndDir($opFile, $this->packageDir);
                array_push($addedFiles, $opFile);
                if ($opBundleFile) {
                    $this->copyFileAndDir($opBundleFile, $this->packageDir);
                    array_push($addedFiles, $opBundleFile);
                }
            }

            if (strpos($op, 'R') === 0) {
                if ($newBundleFile) {
                    $this->copyFileAndDir($newBundleFile, $this->packageDir);
                    array_push($addedFiles, $newBundleFile);
                }
            }
        }

        $this->printLog($addedFiles, 'add');
    }

    private function updatedFiles($fileLines)
    {
        $this->output->writeln('<info>  本次升级将更新以下文件</info>');
        $updatedFiles = array();
        foreach ($fileLines as $line) {
            list($op, $opFile, $newFile) = $this->parseFileLine($line);

            $opBundleFile = $this->getBundleFile($opFile);

            if ($op == 'M') {
                $this->copyFileAndDir($opFile, $this->packageDir);
                array_push($updatedFiles, $opFile);
                if ($opBundleFile) {
                    $this->copyFileAndDir($opBundleFile, $this->packageDir);
                    array_push($updatedFiles, $opBundleFile);
                }
            }
        }

        $this->printLog($updatedFiles, 'update');
    }

    private function deletedFiles($fileLines)
    {
        $this->output->writeln('<info>  本次升级将删除以下文件</info>');
        $deletedFiles = array();
        foreach ($fileLines as $line) {
            list($op, $opFile, $newFile) = $this->parseFileLine($line);

            $opBundleFile = $this->getBundleFile($opFile);

            if ($op == 'D') {
                $this->insertDelete($opFile, $this->packageDir);
                array_push($deletedFiles, $opFile);
                if ($opBundleFile) {
                    $this->insertDelete($opBundleFile, $this->packageDir);
                    array_push($deletedFiles, $opFile);
                }
            }

            if (strpos($op, 'R') === 0) {
                if ($opBundleFile) {
                    $this->insertDelete($opBundleFile, $this->packageDir);
                    array_push($deletedFiles, $opFile);
                }
            }
        }

        $this->printLog($deletedFiles, 'delete');
    }

    private function migrationFiles($fileLines)
    {
        $this->output->writeln('<info>  正在检测migrations目录文件</info>');
        $migrationFiles = array();
        foreach ($fileLines as $line) {
            list($op, $opFile, $newFile) = $this->parseFileLine($line);

            if (preg_match('/^(\w+.*\/)?migrations\/\d+.*\.php$/', $opFile, $matches) === 1) {
                array_push($migrationFiles, $opFile);
            }
        }

        $this->printLog($migrationFiles, 'migration');
        if (count($migrationFiles)) {
            if ($this->input->isInteractive() && !$this->askConfirmation("<comment>请根据以上sql脚本完成 scripts/upgrade-{$this->version}.php,完成后输入y (y/n)</comment>")) {
                $this->output->writeln('<error> 制作升级包终止!</error>');
                exit;
            }
        }
    }

    private function webAssetsFiles($fileLines)
    {
        $this->output->writeln('<info>  正在检测web/assets/libs目录文件</info>');

        $webAssetsFiles = array();
        foreach ($fileLines as $line) {
            list($op, $opFile, $newFile) = $this->parseFileLine($line);
            if (strpos($opFile, 'web/assets/libs') === 0) {
                array_push($webAssetsFiles, $op.' '.$opFile);
            }
        }

        $this->printLog($webAssetsFiles, 'web/assets/libs');

        if (count($webAssetsFiles)) {
            if ($this->input->isInteractive() && !$this->askConfirmation('<comment> web/assets/libs下的文件有修改，需要在发布版本中修改seajs-global-config.js升级版本号！修改后请输入y (y/n)</comment>')) {
                $this->output->writeln('<error>制作升级包终止!</error>');
                exit;
            }
        }
    }

    /**
     * @param $migrationFiles
     * @param $operation
     */
    private function printLog($migrationFiles, $operation)
    {
        foreach ($migrationFiles as $file) {
            $this->output->writeln("    - {$operation} file: {$file}");
        }
        if (!count($migrationFiles)) {
            $this->output->writeln('    - --');
        }
    }

    /**
     * format:  file mode  | file name
     *
     * @param $line
     *
     * @return array
     */
    private function parseFileLine($line)
    {
        $splitLine = preg_split('/\s+/', $line);
        list($op, $opFile, $newFile) = $splitLine;

        return array($op, $opFile, $newFile);
    }
}
