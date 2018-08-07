<?php

namespace Codeages\Biz\Framework\Scheduler\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class TableCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('scheduler:table')
            ->setDescription('Create a migration for the scheduler database table')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $migrations = array(
            'scheduler',
            'scheduler_delete_fields',
            'scheduler_add_retry_num_and_job_detail',
            'scheduler_update_job_detail',
            'scheduler_update_pool',
            'scheduler_add_job_fired_index',
            'scheduler_add_message_and_trace',
            'scheduler_add_job_process',
            'scheduler_delete_fired_log_job',
            'scheduler_add_job_fired_name',
            'scheduler_add_sys_pid',
        );

        foreach ($migrations as $migration) {
            $this->copyNextMigration($directory, $migration);
        }

        $output->writeln('<info>Migration created successfully!</info>');
    }

    protected function copyNextMigration($directory, $next)
    {
        if (!$this->existMigration($directory, $next)) {
            $this->generateMigration($directory, 'biz_'.$next, __DIR__."/stub/{$next}.migration.stub");
        }
    }
}
