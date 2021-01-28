<?php

namespace AppBundle\Command;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncLearnedTaskNumCommand extends BaseCommand
{
    protected $output;

    protected function configure()
    {
        $this->setName('sync:course-member-learned-num')
            ->addArgument('courseId', InputArgument::REQUIRED, 'CourseId');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $courseId = $input->getArgument('courseId');

        $userIds = $this->getCourseMemberService()->findMemberUserIdsByCourseId($courseId);
        foreach ($userIds as $userId) {
            $this->getCourseService()->recountLearningData($courseId, $userId);
        }
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
