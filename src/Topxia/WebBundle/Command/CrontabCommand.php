<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Topxia\Service\User\CurrentUser;
use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\BlockToolkit;

class CrontabCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName ( 'crontab:schedule' );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setDisableWebCrontab();
        $logger = $this->getContainer()->get('logger');
        $logger->info('Crontab:开始执行定时任务');
        $this->initServiceKernel();
        $this->getServiceKernel()->createService('Crontab.CrontabService')->scheduleJobs();
        $logger->info('Crontab:定时任务执行完毕');
    }

    protected function setDisableWebCrontab()
    {
        $setting = $this->getSettingService()->get('magic', array());
        if (empty($setting['disable_web_crontab'])) {
            $setting['disable_web_crontab'] = 1;
            $this->getSettingService()->set('magic',$setting); 
        }
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}