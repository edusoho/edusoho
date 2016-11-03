<?php
namespace Topxia\WebBundle\Command;

use Topxia\Common\BlockToolkit;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\AssetsInstallCommand;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class WarmupCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('unit:redis-warmup');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始初始化系统</info>');
        $users = $this->getUserService()->searchUsers(array(), null, 0, 100000);
        foreach ($users as $user) {
			$this->getUserService()->getUser($user['id']);
			$this->getUserService()->getUserByNickname($user['nickname']);
			$this->getUserService()->getUserByEmail($user['email']);
        }
    }

    protected function getUserService()
    {
    	return ServiceKernel::instance()->createService('User.UserService');
    }
}