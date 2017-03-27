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
        $this->setName('util:redis-warmup');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $output->writeln('<info>开始初始化系统</info>');
        $users = $this->getUserService()->searchUsers(array(), array(), 0, 100000);
        $time = time();
        ServiceKernel::instance()->getConnection()->update('user', array('updatedTime' => $time), array(1=>1));

        $this->getUserDao()->updateUser(1, array('updatedTime'=>$time));
        foreach ($users as $user) {
			$this->getUserService()->getUser($user['id']);
			$this->getUserService()->getUserByNickname($user['nickname']);
            $this->getUserService()->getUserByEmail($user['email']);

            $this->getServiceKernel()->createDao('Course.CourseMemberDao')->getMemberByCourseIdAndUserId(1, $user['id']);

            $this->getServiceKernel()->createDao('Course.LessonLearnDao')->getLearnByUserIdAndLessonId($user['id'], 1);
        }


        $this->getCacheService()->get('settings');

        $this->getServiceKernel()->createService('Content.NavigationService')->getOpenedNavigationsTreeByType('friendlyLink');

        $this->getServiceKernel()->createService('Theme.ThemeService')->getCurrentThemeConfig();

        $this->getServiceKernel()->createService('Content.BlockService')->getBlockByCode('jianmo:home_top_banner');

        $this->getServiceKernel()->createDao('Classroom:Classroom.ClassroomCourseDao')->findClassroomByCourseId(1);
    }

    protected function getCacheService()
    {
        return $this->getServiceKernel()->createService('System.CacheService');
    }

    protected function getUserService()
    {
    	return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getUserDao()
    {
        return ServiceKernel::instance()->createDao('User.UserDao');
    }
}