<?php

namespace Codeages\Biz\Framework\Pay\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class TableCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('pay:table')
            ->setDescription('Create a migration for the pay database table')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $this->ensureMigrationDoseNotExist($directory, 'biz_pay');

        $filepath = $this->generateMigrationPath($directory, 'biz_pay');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/pay.migration.stub'));

        $output->writeln('<info>Migration created successfully!</info>');
    }
}
