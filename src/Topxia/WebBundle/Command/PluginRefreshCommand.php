<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\Util\PluginUtil;

class PluginRefreshCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName ( 'plugin:refresh' );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        PluginUtil::refresh();
        $output->writeln("<info>刷新成功....</info>");
    }

}