<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;

use Symfony\Component\Console\Input\InputArgument;
class TestUserCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName ( 'topxia:testuser' )
        ->addArgument(
            'verifiedMobile',
            InputArgument::OPTIONAL
        )
        ->addArgument(
            'password',
            InputArgument::OPTIONAL
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>添加测试用户开始</info>');

        $this->initServiceKernel();
        $verifiedMobile = $input->getArgument('verifiedMobile');
        $password = $input->getArgument('password');
        $user = array(
            'verifiedMobile' => $verifiedMobile,
            'password' => $password
        );
        $user['nickname'] = '体验管理员';
        $user['email'] = $verifiedMobile.'@example.com';
        $user = $this->getUserService()->register($user);
        $this->getUserService()->changeUserRoles($user['id'],array('ROLE_USER','ROLE_TEACHER','ROLE_ADMIN','ROLE_SUPER_ADMIN'));
        $this->getUserService()->lockUser(1);
        $output->writeln('<info>添加测试用户完毕</info>');
    }

    

    private function initServiceKernel()
    {
        $serviceKernel = ServiceKernel::create('dev', false);
        $serviceKernel->setParameterBag($this->getContainer()->getParameterBag());

        $serviceKernel->setConnection($this->getContainer()->get('database_connection'));
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' =>  '127.0.0.1',
            'roles' => array(),
        ));
        $serviceKernel->setCurrentUser($currentUser);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

}