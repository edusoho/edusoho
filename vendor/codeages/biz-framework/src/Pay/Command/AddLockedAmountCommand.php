<?php

namespace Codeages\Biz\Framework\Pay\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class AddLockedAmountCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('pay:add_locked_amount')
            ->setDescription('Create a migration for add locked_amount field')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $this->ensureMigrationDoseNotExist($directory, 'add_locked_coin');

        $filepath = $this->generateMigrationPath($directory, 'add_locked_coin');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/add_locked_amount.migration.tab'));

        $output->writeln('<info>Migration created successfully!</info>');
    }
}
