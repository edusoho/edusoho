<?php

namespace AppBundle\Command;

use Codeages\Beanstalk\Client;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestPutJobCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('test:put-job')
            ->addArgument('topic', InputArgument::REQUIRED, 'job topic')
            ->addArgument('body', InputArgument::REQUIRED, 'job body');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Start put {$input->getArgument('topic')}.</info>");
        $beanstalk = new Client();

        $beanstalk->connect();
        $beanstalk->useTube($input->getArgument('topic'));

        $beanstalk->put(
            500, // Give the job a priority of 23.
            0,  // Do not wait to put job into the ready queue.
            60, // Give the job 1 minute to run.
            $input->getArgument('body')
        );

        $beanstalk->disconnect();

        $output->writeln('<info>End.</info>');
    }
}
