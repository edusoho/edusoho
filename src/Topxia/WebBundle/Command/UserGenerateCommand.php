<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;

class UserGenerateCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName ( 'topxia:user-generate' );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        for ($i=0; $i < 50; $i++) { 
            $user = array();
            $user['nickname'] = 'test_' . $i;
            $user['password'] = 'abcde';
            $user['email'] = $user['nickname'] . '@edusoho.com';
            $this->getUserService()->register($user);
        }

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