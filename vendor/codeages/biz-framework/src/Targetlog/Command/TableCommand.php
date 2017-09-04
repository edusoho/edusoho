<?php

namespace Codeages\Biz\Framework\Targetlog\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class TableCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('targetlog:table')
            ->setDescription('Create a migration for the targetlog database table')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $this->ensureMigrationDoseNotExist($directory, 'biz_targetlog');

        $filepath = $this->generateMigrationPath($directory, 'biz_targetlog');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/targetlog.migration.stub'));

        $output->writeln('<info>Migration created successfully!</info>');
    }
}
