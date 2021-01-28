<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;

class CopyInstallFilesCommand extends BaseCommand
{
    protected function configure()
    {
        $this->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'version?'
            )->setName('topxia:copy-install-files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>copy-install-files开始</info>');

        $rootDir = $this->getContainer()->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'../';

        $version = $input->getArgument('version');
        $fileSystem = new Filesystem();

        $fileSystem->remove("{$rootDir}/build/edusoho-{$version}.tar.gz");

        $fileSystem->mirror("{$rootDir}/installFiles/data", "{$rootDir}/build/edusoho/.", null, array(
            'override' => true,
        ));
        $output->writeln('<info>copy installFiles/data/* to build/edusoho</info>');

        $output->writeln('<info>copy-install-files结束</info>');
    }
}
