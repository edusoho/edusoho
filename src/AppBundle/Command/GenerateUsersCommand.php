<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Service\Common\ServiceKernel;

class GenerateUsersCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:generate-users')
            ->addArgument('index', InputArgument::REQUIRED, '数量')
            ->addArgument('start', InputArgument::OPTIONAL, '起始值');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $index = $input->getArgument('index');
        $start = $input->getArgument('start', 0);

        for ($i = $start; $i < $index; ++$i) {
            $user = array();
            $user['nickname'] = 'test_'.$i;
            $user['password'] = 'abcde';
            $user['email'] = $user['nickname'].'@edusoho.com';
            $this->getUserService()->register($user);
        }
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
