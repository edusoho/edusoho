<?php

namespace AppBundle\Command;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\ActivityDao;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Service\CourseService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\CourseStrategy;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TechSupportSyncClassroomCoursesTaskCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('tech-support:classroom-course-task-sync')
            ->setDescription('处理全站班级课程与原课程课时不同步的问题：--real 同步处理')
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
        $conditions = [
            'locked' => 1,
        ];
        $count = $this->getCourseService()->countCourses($conditions);
        if (empty($count)) {
            $output->writeln('<info>不存在需要同步的课程</info>');

            return;
        }
        $copyCourses = $this->getCourseService()->searchCourses($conditions, ['updatedTime' => 'DESC'], 0, $count, ['id', 'parentId', 'courseSetId']);
        // 原课程下的课时
        $originCourseIds = array_column($copyCourses, 'parentId');
        $originTasks = $this->getTaskService()->searchTasks(['courseIds' => $originCourseIds], [], 0, PHP_INT_MAX, ['id', 'courseId', 'categoryId', 'activityId', 'title']);
        if (empty($originTasks)) {
            $output->writeln('<info>不存在需要同步的课时任务</info>');

            return;
        }
        // 原课程下的章节
        $originChapters = $this->getCourseChapterDao()->search(['courseIds' => $originCourseIds], [], 0, PHP_INT_MAX);
        $originChapters = ArrayToolkit::groupIndex($originChapters, 'courseId', 'id');
        $originTasks = ArrayToolkit::groupIndex($originTasks, 'courseId', 'id');
        // 复制课程下的课时
        $copyCourseIds = array_column($copyCourses, 'id');
        $copyTasks = $this->getTaskService()->searchTasks(['courseIds' => $copyCourseIds], [], 0, PHP_INT_MAX, ['id', 'copyId', 'courseId', 'categoryId']);
        // 复制课程下的章节
        $copyChapters = $this->getCourseChapterDao()->search(['courseIds' => $copyCourseIds], [], 0, PHP_INT_MAX);
        $copyChapters = ArrayToolkit::groupIndex($copyChapters, 'courseId', 'copyId');
        $copyTasks = ArrayToolkit::groupIndex($copyTasks, 'courseId', 'copyId');
        foreach ($copyCourses as $copyCourse) {
            $originTaskIds = isset($originTasks[$copyCourse['parentId']]) ? array_keys($originTasks[$copyCourse['parentId']]) : [];
            $copyTaskIds = isset($copyTasks[$copyCourse['id']]) ? array_keys($copyTasks[$copyCourse['id']]) : [];
            // 删除多余课时
            $toDeleteTaskIds = array_diff($copyTaskIds, $originTaskIds);
            foreach ($toDeleteTaskIds as $toDeleteTaskId) {
                $this->createCourseStrategy($copyCourse)->deleteTask($copyTasks[$copyCourse['id']][$toDeleteTaskId]);
                $output->writeln("<info>执行删除多余的任务:{$toDeleteTaskId}成功</info>");
            }
            // 删除多余章节
            $originChapterIds = isset($originChapters[$copyCourse['parentId']]) ? array_keys($originChapters[$copyCourse['parentId']]) : [];
            $copyChapterIds = isset($copyChapters[$copyCourse['id']]) ? array_keys($copyChapters[$copyCourse['id']]) : [];
            $toDeleteChapterIds = array_diff($copyChapterIds, $originChapterIds);
            $delete = [];
            foreach ($toDeleteChapterIds as $toDeleteChapterId) {
                $delete[] = ['id' => $toDeleteChapterId];
                $output->writeln("<info>待删除多余的章节:{$toDeleteChapterId}</info>");
            }
            if ($delete) {
                $this->getCourseChapterDao()->batchDelete($delete);
            }
            // 创建课时
            $toCopyTaskIds = array_diff($originTaskIds, $copyTaskIds);
            if ($toCopyTaskIds) {
                $toCopyTaskIds = implode(',', $toCopyTaskIds);
                $output->writeln("<info>待创建的任务:{$toCopyTaskIds}</info>");
            }
            // 创建章节
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

    /**
     * @param $course
     *
     * @return CourseStrategy
     */
    private function createCourseStrategy($course)
    {
        return $this->getBiz()->offsetGet('course.strategy_context')->createStrategy($course['courseType']);
    }
}
