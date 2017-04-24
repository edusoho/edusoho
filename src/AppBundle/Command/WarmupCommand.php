<?php

namespace AppBundle\Command;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        ServiceKernel::instance()->getConnection()->update('user', array('updatedTime' => $time), array(1 => 1));

        foreach ($users as $user) {
            $this->getUserService()->getUser($user['id']);
            $this->getUserService()->getUserByNickname($user['nickname']);
            $this->getUserService()->getUserByEmail($user['email']);

            $this->getServiceKernel()->createDao('Course:CourseMemberDao')->getByCourseIdAndUserId(1, $user['id']);

            $this->getServiceKernel()->createDao('Course:LessonLearnDao')->getLearnByUserIdAndLessonId($user['id'], 1);
        }

        $this->getCacheService()->get('settings');

        $this->getServiceKernel()->createService('Content:NavigationService')->getOpenedNavigationsTreeByType('friendlyLink');

        $this->getServiceKernel()->createService('Theme:ThemeService')->getCurrentThemeConfig();

        $this->getServiceKernel()->createService('Content:BlockService')->getBlockByCode('jianmo:home_top_banner');

        $this->getServiceKernel()->createDao('Classroom:Classroom.ClassroomCourseDao')->getClassroomByCourseId(1);
    }

    protected function getCacheService()
    {
        return $this->getContainer()->get('biz')->createService('System:CacheService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
