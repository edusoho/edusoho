<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;

class ResetPasswordCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName('topxia:reset-password')
            ->addArgument('userid', InputArgument::REQUIRED, '用户id')
            ->addArgument('password', InputArgument::REQUIRED, '用户密码')
            ->setDescription('重置用户密码');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $userid = $input->getArgument('userid');
        $password = $input->getArgument('password');
        $output->writeln("<comment>重置用户ID: {$userid} 的密码为：{$password}</comment>");
        $this->getUserService()->changePassword($userid, $password);

        $output->writeln("<info>密码重置成功....</info>");

    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function initServiceKernel()
    {
        $serviceKernel = ServiceKernel::create('dev', false);
        $serviceKernel->setParameterBag($this->getContainer()->getParameterBag());
        $serviceKernel->setConnection($this->getContainer()->get('database_connection'));
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 1,
            'nickname' => '游客',
            'currentIp' =>  '127.0.0.1',
            'roles' => array(),
        ));
        $serviceKernel->setCurrentUser($currentUser);
    }

}
