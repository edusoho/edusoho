<?php
namespace Codeages\Biz\Framework\Queue;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class WorkerCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('queue:work')
            ->setDescription('Start processing jobs on the queue')
            ->addArgument('name', InputArgument::OPTIONAL, 'Queue name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueName = $input->getArgument('name') ? : 'default';

        $queue = $this->biz['queue.'.$queueName];

    }
}