<?php
namespace Topxia\WebBundle\Command;

use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CountOnlineCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:count-online')
            ->addArgument('type', InputArgument::REQUIRED, 'type的值是枚举类型：logined, online')
            ->addArgument('minute', InputArgument::REQUIRED, '分钟');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $type = $input->getArgument('type');
        if (!in_array($type, array('logined', 'online'))) {
            $output->writeln('type参数不正确，type的值是枚举类型：logined, online');
            return;
        }
        $minute = $input->getArgument('minute');

        $currentTime = time();
        $start       = $minute * 60;
        $count       = $this->getServiceKernel()->getRedis()->zCount("session:{$type}", $start, $currentTime);

        $output->write($count);
    }

    private function initServiceKernel()
    {
        $serviceKernel = ServiceKernel::create('dev', false);
        $serviceKernel->setParameterBag($this->getContainer()->getParameterBag());

        $serviceKernel->setConnection($this->getContainer()->get('database_connection'));
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id'        => 0,
            'nickname'  => '游客',
            'currentIp' => '127.0.0.1',
            'roles'     => array()
        ));
        $serviceKernel->setCurrentUser($currentUser);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
