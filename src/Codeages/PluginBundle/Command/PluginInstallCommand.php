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
            ->addArgument('code', InputArgument::REQUIRED, 'Plugin code.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');

        $biz = $this->getContainer()->get('biz');

        $service = $biz->service('CodeagesPluginBundle:AppService');

        $app = $service->installPluginApp($code);

    }


}
