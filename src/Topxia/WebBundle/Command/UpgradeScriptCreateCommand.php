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
        $this->generateUpgradeScripts($version, $mode);
    }

    /**
     * TODO: 添加带分页的脚本文件
     */
    protected function generateUpgradeScripts($version, $mode)
    {
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir').'/../';

        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($rootDir . 'scripts')) {
            $this->generateScriptPath();
        }

        $scriptPath = $rootDir.'/scripts/';
        $scriptFileName = 'upgrade-'.$version;
        $scriptFilePath = $scriptPath.$scriptFileName.'.php';
        if (file_exists($scriptFilePath)) {
            $this->output->writeln("<comment>The version script file already exists</comment>");
        } else {
            $script = $this->getScript();
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

    protected function getScript()
    {
        return file_get_contents(__DIR__."/scripts-tpl/Scripts.twig");
    }
}