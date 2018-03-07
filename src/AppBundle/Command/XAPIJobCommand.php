<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class XAPIJobCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:xapi')
            ->addArgument('jobName', InputArgument::REQUIRED, '任务名称')
            ->setDescription('手动执行 xapi 的定时任务');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $biz = $this->getBiz();
        $jobName = $input->getArgument('jobName');
        $class = "Biz\\Xapi\\Job\\$jobName";
        /** @var \Codeages\Biz\Framework\Scheduler\AbstractJob $instance */
        $instance = new $class(array(), $biz);
        $instance->execute();
    }


}
