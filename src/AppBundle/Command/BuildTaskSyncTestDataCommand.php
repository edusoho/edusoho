<?php

namespace AppBundle\Command;

use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildTaskSyncTestDataCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('test-data:task-sync')
            ->addArgument('count', InputArgument::REQUIRED)
            ->addArgument('courseId', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $counter = 0;
        while ($counter < $input->getArgument('count')) {
            $classroom = $this->getClassroomService()->addClassroom(['title' => '测试同步' . $counter]);
            $this->getClassroomService()->addCoursesToClassroom($classroom['id'], [$input->getArgument('courseId')]);
            $output->writeln("<info>创建班级《{$classroom['title']}》成功</info>");
            $counter++;
        }
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }
}
