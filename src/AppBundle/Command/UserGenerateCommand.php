<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Service\Common\ServiceKernel;

class UserGenerateCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('topxia:user-generate');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        for ($i = 0; $i < 50; ++$i) {
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
