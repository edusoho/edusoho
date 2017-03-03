<?php

namespace AppBundle\Command;

use Biz\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class CreateTestDataCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('test:create-data')
             ->addArgument('start', InputArgument::REQUIRED, '数据的起始索引')
             ->addArgument('num', InputArgument::REQUIRED, '数据个数');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $num = $input->getArgument('num');
        $start = $input->getArgument('start');

        $this->initServiceKernel();
        $user = $this->getUserService()->getUserByEmail('test@edusoho.com');
        $this->authenticateUser($user);

        for ($i = $start; $i < $num; ++$i) {
            $user = array(
                'email' => "canuo{$i}@qq.com",
                'nickname' => "canuo{$i}",
                'password' => '111111',
                'createdIp' => '127.0.0.1',
                'type' => 'import',
            );
            $user = $this->getAuthService()->register($user);

            $course = array(
                'title' => "课程测试{$i}",
                'buyable' => '1',
                'type' => 'normal',
            );
            $course = $this->getCourseService()->createCourse($course);

            $this->getCourseService()->publishCourse($course['id']);

            $this->getCourseMemberService()->becomeStudent($course['id'], $user['id']);

            for ($j = 0; $j < 5; ++$j) {
                $lesson = array(
                    'courseId' => $course['id'],
                    'title' => "测试课时{$j}",
                    'type' => 'text',
                    'content' => '课时内容',
                    'summary' => '课时内容',
                );
                $lesson = $this->getCourseService()->createLesson($lesson);

                $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);
            }
        }
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course:CourseService');
    }

    protected function getAuthService()
    {
        return ServiceKernel::instance()->createService('User:AuthService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getCourseMemberService()
    {
        return ServiceKernel::instance()->createService('Course:MemberService');
    }

    protected function authenticateUser($user)
    {
        $user['currentIp'] = '127.0.0.1';
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);

        ServiceKernel::instance()->setCurrentUser($currentUser);

        $token = new UsernamePasswordToken($currentUser, null, 'main', $currentUser['roles']);
        $this->getContainer()->get('security.token_storage')->setToken($token);

        // $loginEvent = new InteractiveLoginEvent($this->getRequest(), $token);
        // $this->get('event_dispatcher')->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);

        // $sessionId = $this->getContainer()->get('request')->getSession()->getId();
        // $this->getUserService()->rememberLoginSessionId($user['id'], $sessionId);
    }
}
