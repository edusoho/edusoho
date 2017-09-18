<?php

namespace Codeages\Biz\Framework\Queue\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class TableCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('queue:table')
            ->setDescription('Create a migration for the queue database table')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $this->ensureMigrationDoseNotExist($directory, 'biz_queue_job');
        $this->ensureMigrationDoseNotExist($directory, 'biz_queue_failed_job');

        $filepath = $this->generateMigrationPath($directory, 'biz_queue_job');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/job.migration.stub'));

        $filepath = $this->generateMigrationPath($directory, 'biz_queue_failed_job');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/failed_job.migration.stub'));

        $output->writeln('<info>Migration created successfully!</info>');
    }
}
