<?php

namespace Codeages\Biz\Framework\Setting\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class SetCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('setting:set')
            ->setDescription('Set setting data.')
            ->addArgument('name', InputArgument::REQUIRED, 'Setting name')
            ->addArgument('data', InputArgument::REQUIRED, 'Setting data')
            ->addOption('yes', null, InputOption::VALUE_NONE, 'real set');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $data = $input->getArgument('data');
        $needAsk = !$input->getOption('yes');

        $oldData = $this->getSettingService()->get($name);

        $output->writeln("Setting name: {$name}");
        $output->writeln('Old data: '.var_export($oldData, true));
        $output->writeln('New data: '.var_export($data, true));

        if ($needAsk) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('<question>Real change setting data ? (y/n)</question>', false);

            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }

        $this->getSettingService()->set($name, $data);

        $output->writeln('New data setted.');
    }

    protected function getSettingService()
    {
        return $this->biz->service('Setting:SettingService');
    }
}
