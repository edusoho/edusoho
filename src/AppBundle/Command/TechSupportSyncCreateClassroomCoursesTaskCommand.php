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
        $biz = $this->getBiz();
        $real = $input->getOption('real');
        $courseId = $input->getArgument('courseId');
        $course = $this->getCourseService()->getCourse($courseId);
        if (1 != $course['locked']) {
            $output->writeln('<info>输入的课程不是班级课程，执行结束</info>');

            return;
        }
        $copiedTaskIds = array_column($this->getTaskService()->searchTasks(['courseId' => $courseId], [], 0, PHP_INT_MAX, ['copyId']), 'copyId');
        $originTaskIds = array_column($this->getTaskService()->searchTasks(['courseId' => $course['parentId']], [], 0, PHP_INT_MAX, ['id']), 'id');
        $toCopyTaskIds = array_diff($originTaskIds, $copiedTaskIds);
        if (empty($toCopyTaskIds)) {
            $output->writeln('<info>不存在问题数据,无需处理</info>');
            $output->writeln('<info>结束</info>');

            return;
        }
        foreach ($toCopyTaskIds as $toCopyTaskId) {
            $originTask = $this->getTaskService()->getTask($toCopyTaskId);
            $output->writeln("<info>未同步创建的任务:{$originTask['id']},《{$originTask['title']}》</info>");
            if (!$real) {
                continue;
            }
            $copiedChapter = $this->getCourseChapterDao()->getByCopyIdAndLockedCourseId($originTask['categoryId'], $courseId);
            if (empty($copiedChapter)) {
                $originChapter = $this->getCourseChapterDao()->get($originTask['categoryId']);
                $originChapter['copyId'] = $originChapter['id'];
                unset($originChapter['id']);
                $originChapter['courseId'] = $courseId;
                $copiedChapter = $this->getCourseChapterDao()->create($originChapter);
            }
            $copiedActivity = $this->getActivityDao()->getByCopyIdAndCourseSetId($originTask['activityId'], $course['courseSetId']);
            if (empty($copiedActivity)) {
                $originActivity = $this->getActivityDao()->get($originTask['activityId']);
                $originActivity['copyId'] = $originActivity['id'];
                unset($originActivity['id']);
                $originActivity['fromCourseId'] = $courseId;
                $originActivity['fromCourseSetId'] = $course['courseSetId'];
                $originActivity['createdTime'] = $copiedChapter['createdTime'];
                $originActivity['updatedTime'] = $copiedChapter['updatedTime'];
                $copiedActivity = $this->getActivityDao()->create($originActivity);
            }
            $task = ArrayToolkit::parts($originTask, [
                'seq',
                'title',
                'isFree',
                'isOptional',
                'startTime',
                'endTime',
                'mode',
                'isLesson',
                'status',
                'number',
                'type',
                'mediaSource',
                'maxOnlineNum',
                'length',
                'syncId',
            ]);
            $task['courseId'] = $courseId;
            $task['categoryId'] = $copiedChapter['id'];
            $task['activityId'] = $copiedActivity['id'];
            $task['copyId'] = $toCopyTaskId;
            $task['fromCourseSetId'] = $course['courseSetId'];
            $task['createdUserId'] = $copiedActivity['fromUserId'];
            $task['createdTime'] = $copiedChapter['createdTime'];
            $task['updatedTime'] = $copiedChapter['updatedTime'];
            $task = $this->getTaskDao()->create($task);
            $output->writeln("<info>创建未同步的任务:{$task['id']}成功</info>");
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
