<?php

namespace AppBundle\Command;

use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\CourseStrategy;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TechSupportSyncDeleteClassroomCoursesTaskCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('tech-support:classroom-course-task-sync-delete')
            ->setDescription('处理班级课程出现原课程删除了课时但是个别班级课程没有同步删除成功的问题[脚本为8.6.21新增]：--real 真正删除')
            ->addArgument(
                'courseId',
                InputArgument::REQUIRED,
                '班级内的课程计划ID（注意：不是原课程ID）'
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
        $course = $this->getCourseService()->getCourse($courseId);
        if (1 != $course['locked']) {
            $output->writeln('<info>输入的课程不是班级课程，执行结束</info>');

            return;
        }
        $sql = "SELECT copytask.* FROM course_task copytask LEFT JOIN course_task oritask ON oritask.id=copytask.copyId WHERE copytask.copyId != 0 AND oritask.id IS NULL AND copytask.courseId={$courseId};";
        $results = $biz['db']->fetchAll($sql);
        $sql = "SELECT copychapter.* FROM course_chapter copychapter LEFT JOIN course_chapter orichapter ON orichapter.id=copychapter.copyId WHERE copychapter.copyId != 0 AND orichapter.id IS NULL AND copychapter.courseId={$courseId};";
        $copyChapters = $biz['db']->fetchAll($sql);
        if (empty($results) && empty($copyChapters)) {
            $output->writeln('<info>不存在问题数据,无需处理</info>');
            $output->writeln('<info>结束</info>');

            return;
        }

        foreach ($results as $task) {
            $output->writeln("<info>待删除的多余的任务:{$task['id']},《{$task['title']}》</info>");
            if ($real) {
                $this->createCourseStrategy($course)->deleteTask($task);
                $output->writeln("<info>删除多余的任务:{$task['id']}成功</info>");
            }
        }
        foreach ($copyChapters as $chapter) {
            $output->writeln("<info>待删除的多余的章节:{$chapter['id']},《{$chapter['title']}》</info>");
            if ($real) {
                $this->getCourseService()->deleteChapter($chapter['courseId'], $chapter['id']);
                $output->writeln("<info>删除多余的章节:{$chapter['id']}成功</info>");
            }
        }
        $output->writeln('<info>结束</info>');
    }

    /**
     * @param $course
     *
     * @return CourseStrategy
     */
    private function createCourseStrategy($course)
    {
        return $this->getBiz()->offsetGet('course.strategy_context')->createStrategy($course['courseType']);
    }

    /**
     * @return TaskService
     */
    public function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
