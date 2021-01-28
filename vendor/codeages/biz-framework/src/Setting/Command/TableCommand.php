<?php

namespace Codeages\Biz\Framework\Setting\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class TableCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('setting:table')
            ->setDescription('Create a migration for the setting database table')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $this->ensureMigrationDoseNotExist($directory, 'biz_setting');

        $filepath = $this->generateMigrationPath($directory, 'biz_setting');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/setting.migration.stub'));

        $output->writeln('<info>Migration created successfully!</info>');
    }
}
