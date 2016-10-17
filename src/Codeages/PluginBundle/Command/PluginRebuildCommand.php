<?php

namespace Codeages\PluginBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PluginRebuildCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('plugin:rebuild')
            ->setDescription('...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $biz = $this->getContainer()->get('biz');

        $service = $biz->service('CodeagesPluginBundle:AppService');

        $app = $service->rebuildPlugins();
    }

}
