<?php

namespace AppBundle\Command;

use Biz\Task\Service\TaskService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TaskListNullFixCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('prod:task-list-null-fix')
            ->setDescription('处理班级课程出现课时为null的数据处理[脚本为8.6.21新增]：--real 真正删除')
            ->addArgument(
                'courseId',
                InputArgument::REQUIRED
            )
            ->addOption(
                'real',
                InputArgument::OPTIONAL
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始</info>');
        $this->initServiceKernel();
        $biz = $this->getBiz();
        $real = $input->getOption('real');
        $courseId = $input->getArgument('courseId');
        $sql = "SELECT course_task.id AS id,course_task.title AS title FROM course_task LEFT JOIN course_chapter ON course_task.categoryId = course_chapter.id WHERE course_task.courseId = {$courseId} AND course_chapter.id IS NULL;";
        $results = $biz['db']->fetchAll($sql);
        if (empty($results)) {
            $output->writeln('<info>不存在问题数据,无需处理</info>');
            $output->writeln('<info>结束</info>');

            return;
        }

        foreach ($results as $task) {
            $output->writeln("<info>待删除的多余的任务:{$task['id']},《{$task['title']}》</info>");
            if ($real) {
                $this->getTaskService()->deleteTask($task['id']);
                $output->writeln("<info>删除多余的任务:{$task['id']}成功</info>");
            }
        }
        $output->writeln('<info>结束</info>');
    }

    /**
     * @return TaskService
     */
    public function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
