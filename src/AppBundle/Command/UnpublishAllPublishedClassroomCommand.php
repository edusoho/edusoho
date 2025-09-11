<?php

namespace AppBundle\Command;

use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UnpublishAllPublishedClassroomCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('unpublish-all-published-classroom');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $classrooms = $this->getClassroomService()->searchClassrooms(['status' => 'published'], [], 0, PHP_INT_MAX, ['id', 'title']);
        foreach ($classrooms as $classroom) {
            $this->getClassroomService()->unpublishedClassroom($classroom['id']);
            $output->writeln("<info>下架班级 {$classroom['title']} 成功</info>");
        }
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
