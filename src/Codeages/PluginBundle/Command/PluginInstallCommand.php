<?php

namespace Codeages\PluginBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PluginInstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('plugin:install')
            ->setDescription('...')
            ->addArgument('name', InputArgument::REQUIRED, 'Plugin name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        

        var_dump($name);

        $output->writeln('Command result.');
    }

}
