<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Biz\User\CurrentUser;
use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\BlockToolkit;

class CrontabCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName ( 'topxia:crontab' );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始执行定时任务</info>');
        $this->initServiceKernel();
        $this->getServiceKernel()->createService('Crontab:CrontabService')->scheduleJobs();
        $output->writeln('<info>定时任务执行完毕</info>');
    }
}