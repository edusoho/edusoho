<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Topxia\Service\Common\ServiceKernel;

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

        $output->writeln('<info>密码重置成功....</info>');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
