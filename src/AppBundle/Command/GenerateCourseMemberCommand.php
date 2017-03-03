<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Topxia\Service\Common\ServiceKernel;

class GenerateCourseMemberCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:generate-course-member')
            ->addArgument('courseId', InputArgument::REQUIRED, '课程id')
            ->addArgument('index', InputArgument::REQUIRED, '数量');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $index = $input->getArgument('index');
        $courseId = $input->getArgument('courseId');

        $course = $this->getCourseService()->getCourse($courseId);

        for ($i = 0; $i < $index; ++$i) {
            $user = $this->getUserService()->getUserByLoginField('test_'.$i);

            if (!empty($user)) {
                $member = array(
                    'courseId' => $course['id'],
                    'userId' => $user['id'],
                    'role' => 'student',
                    'createdTime' => time(),
                );

                $this->becomeStudent($member);

                $output->writeln('<info>第'.($i + 1).'个课程添加</info>');
            }
        }

        $output->writeln('<info>添加学员数据完毕</info>');
    }

    protected function becomeStudent($member)
    {
        $orderFileds = array(
            'price' => 0,
            'remark' => '',
            'isAdminAdded' => 1,
        );

        list($course, $member, $order) = $this->getCourseMemberService()->becomeStudentAndCreateOrder($member['userId'], $member['courseId'], $orderFileds);
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course:MemberService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }
}
