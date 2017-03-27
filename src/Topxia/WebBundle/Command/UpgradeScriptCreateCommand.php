<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class UpgradeScriptCreateCommand extends BaseCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    private $rootDir;

    protected function configure()
    {
        $this->setName('util:upgrade-script-create')
            ->addArgument('version', InputArgument::REQUIRED, '要创建的版本号')
            ->addArgument('mode', InputArgument::OPTIONAL, '模式')
            ->setDescription('用于命令行中创建升级脚本');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getArgument('version');
        $mode = $input->getArgument('mode');
        $this->output = $output;
        $this->rootDir = $rootDir = $this->getContainer()->getParameter('kernel.root_dir').'/../';
        $this->generateUpgradeScripts($version, $mode);
    }

    protected function generateUpgradeScripts($version, $mode)
    {

        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($this->rootDir . 'scripts')) {
            $this->generateScriptPath();
        } else {
            $this->updateScriptPath();
        }

        $scriptPath = $this->rootDir.'/scripts/';
        $scriptFileName = 'upgrade-'.$version;
        $scriptFilePath = $scriptPath.$scriptFileName.'.php';
        if (file_exists($scriptFilePath)) {
            $this->output->writeln("<comment>The version script file already exists</comment>");
        } else {
            $script = $this->getTplScript();
            file_put_contents($scriptFilePath, $script);
            $this->output->writeln("<comment>the script file has created, please take care of it</comment>");
        }
    }

    protected function generateScriptPath()
    {
        $this->output->writeln("<comment>clone scripts....</comment>");
        exec('git clone git@coding.codeages.net:edusoho/upgradescripts.git scripts');
        $this->output->writeln("<comment>scripts directory created</comment>");
    }

    protected function updateScriptPath()
    {
        $this->output->writeln("<comment>git pull scripts....</comment>");
        exec('git pull');
        $this->output->writeln("<comment>the scripts have updated</comment>");
    }

    protected function getTplScript()
    {
        return file_get_contents($this->rootDir."/scripts/upgrade.php.tpl");
    }
}