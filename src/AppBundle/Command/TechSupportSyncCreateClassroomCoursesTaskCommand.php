<?php

namespace AppBundle\Command;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\ActivityDao;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Service\CourseService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TechSupportSyncCreateClassroomCoursesTaskCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('tech-support:classroom-course-task-sync-create')
            ->setDescription('处理班级课程出现原课程创建了课时但是个别班级课程没有同步创建成功的问题：--real 真正创建')
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
        $real = $input->getOption('real');
        $courseId = $input->getArgument('courseId');
        $returnStrings = $this->getTaskService()->syncClassroomCourseTasks($courseId, $real);
        foreach ($returnStrings as $returnString) {
            $output->writeln($returnString);
        }
    }

    /**
     * @return TaskService
     */
    public function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->getBiz()->dao('Task:TaskDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseChapterDao
     */
    protected function getCourseChapterDao()
    {
        return $this->getBiz()->dao('Course:CourseChapterDao');
    }

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return $this->getBiz()->dao('Activity:ActivityDao');
    }
}
