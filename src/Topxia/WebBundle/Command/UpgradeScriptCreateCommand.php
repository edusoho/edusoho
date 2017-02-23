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
            ->addArgument('withPage', InputArgument::OPTIONAL, '是否需要分页')
            ->setDescription('用于命令行中创建升级脚本');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getArgument('version');
        $this->output = $output;
    }

    protected function generateScripts()
    {
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir').'/../';

        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($rootDir.'scripts')) {
            exec('git clone git@coding.codeages.net:edusoho/upgradescripts.git scripts');
        }
    }

    protected function generateUpgradeScripts($version)
    {
        $rootDir = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../');

        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($rootDir.'scripts')) {
            $this->output->writeln("<comment>clone scripts....</comment>");
            exec('git clone git@coding.codeages.net:edusoho/upgradescripts.git scripts');
            $this->output->writeln("<comment>scripts directory created</comment>");
        }

        $scriptPath = $rootDir.'/scripts/';
        $scriptFileName = 'upgrade-'.$version;
        $scriptFilePath = $scriptPath.$scriptFileName.'.php';
        if (file_exists($scriptFilePath)) {
            return true;
        } else {
            return false;
        }
    }
}