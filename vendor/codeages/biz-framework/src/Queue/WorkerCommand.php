<?php

namespace Codeages\Biz\Framework\Queue;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class WorkerCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('queue:work')
            ->setDescription('Start processing jobs on the queue')
            ->addArgument('name', InputArgument::OPTIONAL, 'Queue name')
            ->addOption('once', null, InputOption::VALUE_NONE, 'Only process the next job on the queue')
            ->addOption('tries', null, InputOption::VALUE_OPTIONAL, 'The number of seconds a child process can run', 0)
            ->addOption('stop-when-idle', null, InputOption::VALUE_NONE, 'Worker stop when no jobs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueName = $input->getArgument('name') ?: 'default';

        $queue = $this->biz['queue.connection.'.$queueName];

        $options = array(
            'once' => $input->getOption('once'),
            'stop_when_idle' => $input->getOption('stop-when-idle'),
            'tries' => (int) $input->getOption('tries'),
        );

        $worker = new Worker($queue, $this->biz['queue.failer'], $options);
        $worker->run();
    }
}
