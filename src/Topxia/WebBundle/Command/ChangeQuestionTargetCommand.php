<?php
namespace Topxia\WebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\AssetsInstallCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Common\SystemInitializer;


class ChangeQuestionTargetCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('unit:change-question-target');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>变更试题从属关系...</info>');
    }
}
