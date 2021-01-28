<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Service\Common\ServiceKernel;

class TestUserCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('topxia:testuser')
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
        $mobile = $input->getArgument('verifiedMobile');
        $password = $input->getArgument('password');
        $user = array(
            'mobile' => $mobile,
            'verifiedMobile' => $mobile,
            'password' => $password,
        );
        $user['nickname'] = '体验管理员';
        $user['email'] = $mobile.'@example.com';
        $user = $this->getUserService()->register($user, array('email', 'mobile'));
        $this->getUserService()->changeUserRoles($user['id'], array('ROLE_USER', 'ROLE_TEACHER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'));
        $this->getUserService()->lockUser(1);
        $output->writeln('<info>添加测试用户完毕</info>');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
