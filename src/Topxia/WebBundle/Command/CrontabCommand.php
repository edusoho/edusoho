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
        $this->setName ( 'topxia:crontab' );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始执行定时任务</info>');
        $this->initServiceKernel();
        $this->getServiceKernel()->createService('Crontab.CrontabService')->scheduleJobs();
        $output->writeln('<info>定时任务执行完毕</info>');
    }

    protected function initServiceKernel()
	{
		$serviceKernel = ServiceKernel::create('dev', false);
        $serviceKernel->setParameterBag($this->getContainer()->getParameterBag());

        $biz = $this->getContainer()->get('biz');
		$serviceKernel->setBiz($biz);
		$currentUser = new CurrentUser();
		$currentUser->fromArray(array(
		    'id' => 0,
		    'nickname' => '游客',
		    'currentIp' =>  '127.0.0.1',
		    'roles' => array(),
		));
		$serviceKernel->setCurrentUser($currentUser);
	}
}