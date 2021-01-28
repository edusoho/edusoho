<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrontabCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('crontab:schedule');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // 防止已经配置的crontab，报错，以后要删掉
        return;
    }
}
