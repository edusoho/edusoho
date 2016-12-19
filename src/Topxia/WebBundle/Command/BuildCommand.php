<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Topxia\Common\BlockToolkit;
use Topxia\System;

class BuildCommand extends BaseCommand
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    private $rootDirectory;

    private $buildDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;
    private $distDirectory;

    protected function configure()
    {
        $this->setName('build:install-package')
            ->setDescription('自动编制安装包（含演示数据）')
            ->addArgument('domain', InputArgument::REQUIRED, '演示站点域名')
            ->addArgument('user', InputArgument::REQUIRED, '演示站点数据库用户')
            ->addArgument('password', InputArgument::REQUIRED, '数据库密码')
            ->addArgument('database', InputArgument::REQUIRED, '演示站数据库')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $output->writeln('<info>Start build.</info>');
        $this->initBuild($input, $output);

        $this->buildDatabase();

        $this->buildRootDirectory();
        $this->buildApiDirectory();
        $this->buildAppDirectory();
        $this->buildBootstrapDirectory();
        $this->buildSrcDirectory();
        $this->buildVendorDirectory();
        $this->buildVendorUserDirectory();
        $this->buildWebDirectory();
        $this->buildPluginsDirectory();
        $this->buildDefaultBlocks();

        $this->cleanMacosDirectory();
        $this->clean();

        $this->copyInstallFiles();
        $this->package();
        $output->writeln('<info>End build.</info>');
    }

    private function initBuild(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        $this->rootDirectory  = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../');
        $this->buildDirectory = $this->rootDirectory . '/build';

        $this->filesystem = new Filesystem();

        if ($this->filesystem->exists($this->buildDirectory)) {
            $this->filesystem->remove($this->buildDirectory);
        }

        $this->distDirectory = $this->buildDirectory . DIRECTORY_SEPARATOR . 'edusoho';
        $this->filesystem->mkdir($this->distDirectory);
    }

    private function copyInstallFiles()
    {
        $this->output->writeln('copy install files .');

        $command = $this->getApplication()->find('topxia:copy-install-files');

        $input = new ArrayInput(array(
            'command' => 'topxia:copy-install-files',
            'version' => System::VERSION
        ));

        $command->run($input, $this->output);
    }

    private function buildDatabase()
    {
        $this->output->writeln('build database data.');

        $dumpCommand = $this->getApplication()->find('topxia:dump-init-data');

        $input = new ArrayInput(array(
            'command' => 'topxia:dump-init-data',
            'domain'  => $this->input->getArgument('domain'),
            'user'    => $this->input->getArgument('user'),
            'password'=> $this->input->getArgument('password'),
            'database'=> $this->input->getArgument('database')
        ));

        $returnCode = $dumpCommand->run($input, $this->output);

        $this->output->writeln('cut database file');
        $cutCommand = $this->getApplication()->find('topxia:cutfile');

        $input = new ArrayInput(array(
            'command' => 'topxia:cutfile',
            'line'    => 15
        ));

        $returnCode = $cutCommand->run($input, $this->output);

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            '请确认已经将演示数据sql中的cloud_access_key和cloud_secret_key修改为12345（值前面表示字符串长度的数字s:5也要改) Y/N:',
            false
        );

        if(!$helper->ask($this->input, $this->output, $question)){
            $this->output->writeln('<error>制作安装包终止!</error>');
            exit();
        }
    }

    private function package()
    {
        $this->output->writeln('packaging...');

        chdir($this->buildDirectory);

        $command = "tar czvf edusoho-" . System::VERSION . ".tar.gz edusoho/";
        exec($command);
    }

    private function clean()
    {
        $this->output->writeln('cleaning...');
    }

    private function buildRootDirectory()
    {
        $this->output->writeln('build / .');
        $this->filesystem->copy("{$this->rootDirectory}/README.html", "{$this->distDirectory}/README.html");
    }

    private function buildApiDirectory()
    {
        $this->output->writeln('build api/ .');
        $this->filesystem->mkdir("{$this->distDirectory}/api");
        $this->filesystem->mirror("{$this->rootDirectory}/api", "{$this->distDirectory}/api");
    }

    private function buildAppDirectory()
    {
        $this->output->writeln('build app/ .');

        $this->filesystem->mkdir("{$this->distDirectory}/app");
        $this->filesystem->mkdir("{$this->distDirectory}/app/cache");
        $this->filesystem->mkdir("{$this->distDirectory}/app/data");
        $this->filesystem->mkdir("{$this->distDirectory}/app/data/udisk");
        $this->filesystem->mkdir("{$this->distDirectory}/app/data/private_files");
        $this->filesystem->mkdir("{$this->distDirectory}/app/data/upgrade");
        $this->filesystem->mkdir("{$this->distDirectory}/app/data/backup");
        $this->filesystem->mkdir("{$this->distDirectory}/app/logs");
        $this->filesystem->mirror("{$this->rootDirectory}/app/Resources", "{$this->distDirectory}/app/Resources");
        $this->filesystem->mirror("{$this->rootDirectory}/app/config", "{$this->distDirectory}/app/config");

        $this->filesystem->chmod("{$this->distDirectory}/app/cache", 0777);
        $this->filesystem->chmod("{$this->distDirectory}/app/data", 0777);
        $this->filesystem->chmod("{$this->distDirectory}/app/data/udisk", 0777);
        $this->filesystem->chmod("{$this->distDirectory}/app/data/private_files", 0777);
        $this->filesystem->chmod("{$this->distDirectory}/app/data/upgrade", 0777);
        $this->filesystem->chmod("{$this->distDirectory}/app/data/backup", 0777);
        $this->filesystem->chmod("{$this->distDirectory}/app/logs", 0777);

        $this->filesystem->remove("{$this->distDirectory}/app/config/routing_plugins.yml");
        $this->filesystem->touch("{$this->distDirectory}/app/config/routing_plugins.yml");
        $this->filesystem->remove("{$this->distDirectory}/app/config/parameters.yml");

        $this->filesystem->copy("{$this->distDirectory}/app/config/parameters.yml.dist", "{$this->distDirectory}/app/config/parameters.yml");
        $this->filesystem->chmod("{$this->distDirectory}/app/config/parameters.yml", 0777);

        $this->filesystem->remove("{$this->distDirectory}/app/config/parameters.yml.dist");

        $this->filesystem->copy("{$this->rootDirectory}/app/console", "{$this->distDirectory}/app/console");
        $this->filesystem->copy("{$this->rootDirectory}/app/AppCache.php", "{$this->distDirectory}/app/AppCache.php");
        $this->filesystem->copy("{$this->rootDirectory}/app/AppKernel.php", "{$this->distDirectory}/app/AppKernel.php");
        $this->filesystem->copy("{$this->rootDirectory}/app/autoload.php", "{$this->distDirectory}/app/autoload.php");
        $this->filesystem->copy("{$this->rootDirectory}/app/bootstrap.php.cache", "{$this->distDirectory}/app/bootstrap.php.cache");
    }

    public function buildBootstrapDirectory()
    {
        $this->filesystem->mkdir("{$this->distDirectory}/bootstrap");
        $this->filesystem->copy("{$this->rootDirectory}/bootstrap/bootstrap_install.php", "{$this->distDirectory}/bootstrap/bootstrap_install.php");
    }

    public function buildPluginsDirectory()
    {
        $this->output->writeln('build plugins/ .');
        $this->filesystem->mkdir("{$this->distDirectory}/plugins");
    }

    public function buildSrcDirectory()
    {
        $this->output->writeln('build src/ .');
        $this->filesystem->mirror("{$this->rootDirectory}/src", "{$this->distDirectory}/src");

        $this->filesystem->remove("{$this->distDirectory}/src/Topxia/AdminBundle/Resources/public");
        $this->filesystem->remove("{$this->distDirectory}/src/Topxia/WebBundle/Resources/public");
        $this->filesystem->remove("{$this->distDirectory}/src/Topxia/MobileBundle/Resources/public");
        $this->filesystem->remove("{$this->distDirectory}/src/Custom/AdminBundle/Resources/public");
        $this->filesystem->remove("{$this->distDirectory}/src/Custom/WebBundle/Resources/public");
        $this->filesystem->remove("{$this->distDirectory}/src/Classroom/ClassroomBundle/Resources/public");
        $this->filesystem->remove("{$this->distDirectory}/src/MaterialLib/MaterialLibBundle/Resources/public");
        $this->filesystem->remove("{$this->distDirectory}/src/Org/OrgBundle/Resources/public");
        $this->filesystem->remove("{$this->distDirectory}/src/Permission/PermissionBundle/Resources/public");
        $this->filesystem->remove("{$this->distDirectory}/src/SensitiveWord/SensitiveWordBundle/Resources/public");

        $this->filesystem->remove("{$this->distDirectory}/src/Topxia/WebBundle/Command");
        $this->filesystem->mkdir("{$this->distDirectory}/src/Topxia/WebBundle/Command");

        $this->filesystem->mirror("{$this->rootDirectory}/src/Topxia/WebBundle/Command/plugins-tpl", "{$this->distDirectory}/src/Topxia/WebBundle/Command/plugins-tpl");
        $this->filesystem->mirror("{$this->rootDirectory}/src/Topxia/WebBundle/Command/Templates", "{$this->distDirectory}/src/Topxia/WebBundle/Command/Templates");

        $this->filesystem->copy("{$this->rootDirectory}/src/Topxia/WebBundle/Command/BaseCommand.php", "{$this->distDirectory}/src/Topxia/WebBundle/Command/BaseCommand.php");
        $this->filesystem->copy("{$this->rootDirectory}/src/Topxia/WebBundle/Command/BuildPluginAppCommand.php", "{$this->distDirectory}/src/Topxia/WebBundle/Command/BuildPluginAppCommand.php");
        $this->filesystem->copy("{$this->rootDirectory}/src/Topxia/WebBundle/Command/BuildThemeAppCommand.php", "{$this->distDirectory}/src/Topxia/WebBundle/Command/BuildThemeAppCommand.php");
        $this->filesystem->copy("{$this->rootDirectory}/src/Topxia/WebBundle/Command/OldPluginRegisterCommand.php", "{$this->distDirectory}/src/Topxia/WebBundle/Command/OldPluginRegisterCommand.php");
        $this->filesystem->copy("{$this->rootDirectory}/src/Topxia/WebBundle/Command/OldPluginCreateCommand.php", "{$this->distDirectory}/src/Topxia/WebBundle/Command/OldPluginCreateCommand.php");
        $this->filesystem->copy("{$this->rootDirectory}/src/Topxia/WebBundle/Command/OldPluginRefreshCommand.php", "{$this->distDirectory}/src/Topxia/WebBundle/Command/OldPluginRefreshCommand.php");
        $this->filesystem->copy("{$this->rootDirectory}/src/Topxia/WebBundle/Command/ThemeRegisterCommand.php", "{$this->distDirectory}/src/Topxia/WebBundle/Command/ThemeRegisterCommand.php");
        $this->filesystem->copy("{$this->rootDirectory}/src/Topxia/WebBundle/Command/ResetPasswordCommand.php", "{$this->distDirectory}/src/Topxia/WebBundle/Command/ResetPasswordCommand.php");
        $this->filesystem->copy("{$this->rootDirectory}/src/Topxia/WebBundle/Command/Fixtures/PluginAppUpgradeTemplate.php", "{$this->distDirectory}/src/Topxia/WebBundle/Command/Fixtures/PluginAppUpgradeTemplate.php");
        $this->filesystem->copy("{$this->rootDirectory}/src/Topxia/WebBundle/Command/InitAutoOpenSaasCommand.php", "{$this->distDirectory}/src/Topxia/WebBundle/Command/InitAutoOpenSaasCommand.php");

        $finder = new Finder();
        $finder->directories()->in("{$this->distDirectory}/src/");

        $toDeletes = array();

        foreach ($finder as $dir) {
            if ($dir->getFilename() == 'Tests') {
                $toDeletes[] = $dir->getRealpath();
            }
        }

        foreach ($toDeletes as $file) {
            $this->filesystem->remove($file);
        }
    }

    public function buildVendorDirectory()
    {
        $this->output->writeln('build vendor/ .');
        $buildVendorCommand = $this->getApplication()->find('app:build-mini-vendor');
        $input = new ArrayInput(array());
        $returnCode = $buildVendorCommand->run($input, new NullOutput());
        $this->filesystem->mirror("{$this->buildDirectory}/vendor", "{$this->distDirectory}/vendor");
        $this->filesystem->remove("{$this->buildDirectory}/vendor");
    }

    public function buildVendorUserDirectory()
    {
        $this->output->writeln('build vendor_user/ .');
        $this->filesystem->mirror("{$this->rootDirectory}/vendor_user", "{$this->distDirectory}/vendor_user");
    }

    public function buildWebDirectory()
    {
        $this->output->writeln('build web/ .');

        $this->filesystem->mkdir("{$this->distDirectory}/web");
        $this->filesystem->mkdir("{$this->distDirectory}/web/files");
        $this->filesystem->mkdir("{$this->distDirectory}/web/bundles");
        $this->filesystem->mkdir("{$this->distDirectory}/web/themes");
        $this->filesystem->mirror("{$this->rootDirectory}/web/assets", "{$this->distDirectory}/web/assets");
        $this->filesystem->mirror("{$this->rootDirectory}/web/customize", "{$this->distDirectory}/web/customize");
        $this->filesystem->mirror("{$this->rootDirectory}/web/install", "{$this->distDirectory}/web/install");
        $this->filesystem->mirror("{$this->rootDirectory}/web/themes/autumn", "{$this->distDirectory}/web/themes/autumn");
        $this->filesystem->mirror("{$this->rootDirectory}/web/themes/default", "{$this->distDirectory}/web/themes/default");
        $this->filesystem->mirror("{$this->rootDirectory}/web/themes/jianmo", "{$this->distDirectory}/web/themes/jianmo");
        $this->filesystem->mirror("{$this->rootDirectory}/web/themes/default-b", "{$this->distDirectory}/web/themes/default-b");
        $this->filesystem->copy("{$this->rootDirectory}/web/themes/block.json", "{$this->distDirectory}/web/themes/block.json");

        $this->filesystem->copy("{$this->rootDirectory}/web/.htaccess", "{$this->distDirectory}/web/.htaccess");
        $this->filesystem->copy("{$this->rootDirectory}/web/app.php", "{$this->distDirectory}/web/app.php");
        $this->filesystem->copy("{$this->rootDirectory}/web/app_dev.php", "{$this->distDirectory}/web/app_dev.php");
        $this->filesystem->copy("{$this->rootDirectory}/web/favicon.ico", "{$this->distDirectory}/web/favicon.ico");
        $this->filesystem->copy("{$this->rootDirectory}/web/robots.txt", "{$this->distDirectory}/web/robots.txt");
        $this->filesystem->copy("{$this->rootDirectory}/web/crossdomain.xml", "{$this->distDirectory}/web/crossdomain.xml");

        $this->filesystem->chmod("{$this->distDirectory}/web/files", 0777);

        $finder = new Finder();
        $finder->files()->in("{$this->distDirectory}/web/assets/libs");

        foreach ($finder as $file) {
            $filename = $file->getFilename();

            if ($filename == 'package.json' || preg_match('/-debug.js$/', $filename) || preg_match('/-debug.css$/', $filename)) {
                $this->filesystem->remove($file->getRealpath());
            }
        }

        $finder = new Finder();
        $finder->directories()->in("{$this->rootDirectory}/web/bundles")->depth('== 0');
        $needs = array('sensiodistribution', 'topxiaadmin', 'framework', 'topxiaweb', 'customweb', 'customadmin', 'topxiamobilebundlev2', 'classroom', 'sensitiveword', 'materiallib', 'org', 'permission', 'bazingajstranslation');

        foreach ($finder as $dir) {
            if (!in_array($dir->getFilename(), $needs)) {
                continue;
            }

            $this->filesystem->mirror($dir->getRealpath(), "{$this->distDirectory}/web/bundles/{$dir->getFilename()}");
        }
    }

    public function buildDefaultBlocks()
    {
        $this->output->writeln('build default blocks .');

        $themeDir = realpath(__DIR__ . '/../../../../web/themes/');
        BlockToolkit::init("{$themeDir}/block.json", $this->getContainer());
        BlockToolkit::init("{$themeDir}/default/block.json", $this->getContainer());
        BlockToolkit::init("{$themeDir}/autumn/block.json", $this->getContainer());
        BlockToolkit::init("{$themeDir}/jianmo/block.json", $this->getContainer());
    }

    public function cleanMacosDirectory()
    {
        $finder = new Finder();
        $finder->files()->in($this->distDirectory)->ignoreDotFiles(false);

        foreach ($finder as $dir) {
            if ($dir->getBasename() == '.DS_Store') {
                $this->filesystem->remove($dir->getRealpath());
            }
        }
    }
}
