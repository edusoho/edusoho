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
        $originTasks = $this->getTaskService()->searchTasks(['courseIds' => $originCourseIds], [], 0, PHP_INT_MAX, ['id', 'courseId', 'activityId', 'title']);
        $countOriginTasks = count($originTasks);
        if (empty($countOriginTasks)) {
            $output->writeln('<info>不存在需要同步的课时任务</info>');

            return;
        }
        $originTasks = ArrayToolkit::groupIndex($originTasks, 'courseId', 'id');
        // 原课程下的章节
        $originChapters = $this->getCourseChapterDao()->search(['courseIds' => $originCourseIds], [], 0, PHP_INT_MAX);
        $originChapters = ArrayToolkit::groupIndex($originChapters, 'courseId', 'id');
        // 原课程下的任务
        $originActivities = $this->getActivityDao()->search(['ids' => array_column($originTasks, 'activityId')], [], 0, PHP_INT_MAX);
        $originActivities = ArrayToolkit::groupIndex($originActivities, 'fromCourseId', 'id');
        // 复制课程下的课时
        $copyCourseIds = array_column($copyCourses, 'id');
        $copyTasks = $this->getTaskService()->searchTasks(['courseIds' => $copyCourseIds], [], 0, PHP_INT_MAX);
        $copyTasks = ArrayToolkit::groupIndex($copyTasks, 'courseId', 'copyId');
        // 复制课程下的章节
        $copyChapters = $this->getCourseChapterDao()->search(['courseIds' => $copyCourseIds], [], 0, PHP_INT_MAX);
        $copyChapters = ArrayToolkit::groupIndex($copyChapters, 'courseId', 'copyId');
        // 复制课程下的任务
        $copyActivities = $this->getActivityDao()->search(['copyIds' => array_column($originTasks, 'activityId'), 'courseIds' => $copyCourseIds], [], 0, PHP_INT_MAX, ['id', 'fromCourseId', 'copyId']);
        $copyActivities = ArrayToolkit::groupIndex($copyActivities, 'fromCourseId', 'copyId');
        foreach ($copyCourses as $copyCourse) {
            $output->writeln("<info>********** {$copyCourse['id']} **********</info>");
            $originTaskIds = isset($originTasks[$copyCourse['parentId']]) ? array_keys($originTasks[$copyCourse['parentId']]) : [];
            $copyTaskIds = isset($copyTasks[$copyCourse['id']]) ? array_keys($copyTasks[$copyCourse['id']]) : [];
            // 删除多余课时
            $toDeleteTaskIds = array_diff($copyTaskIds, $originTaskIds);
            foreach ($toDeleteTaskIds as $toDeleteTaskId) {
                if ($real) {
                    $this->createCourseStrategy($copyCourse)->deleteTask($copyTasks[$copyCourse['id']][$toDeleteTaskId]);
                    $output->writeln("<info>执行删除多余的课时: {$toDeleteTaskId}成功</info>");
                }
                $output->writeln("<info>待删除多余的课时: {$toDeleteTaskId} {$copyTasks[$copyCourse['id']][$toDeleteTaskId]['title']}</info>");
            }
            // 删除多余章节
            $originChapterIds = isset($originChapters[$copyCourse['parentId']]) ? array_keys($originChapters[$copyCourse['parentId']]) : [];
            $copyChapterIds = isset($copyChapters[$copyCourse['id']]) ? array_keys($copyChapters[$copyCourse['id']]) : [];
            $toDeleteChapterIds = array_diff($copyChapterIds, $originChapterIds);
            foreach ($toDeleteChapterIds as $toDeleteChapterId) {
                $output->writeln("<info>待删除多余的章节: {$toDeleteChapterId}</info>");
            }
            if ($toDeleteChapterIds && $real) {
                $this->getCourseChapterDao()->batchDelete(['copyIds' => $toDeleteChapterIds]);
                $output->writeln("<info>删除多余的章节成功</info>");
            }
            // 创建章节
            $newChapters = [];
            $toCreateChapterIds = array_diff($originChapterIds, $copyChapterIds);
            foreach ($toCreateChapterIds as $toCreateChapterId) {
                $originChapter = $originChapters[$copyCourse['parentId']][$toCreateChapterId];
                $originChapter['copyId'] = $originChapter['id'];
                unset($originChapter['id']);
                $originChapter['courseId'] = $copyCourse['id'];
                $newChapters[] = $originChapter;
                $output->writeln("<info>待创建的章节: {$toCreateChapterId}</info>");
            }
            if ($newChapters && $real) {
                $this->getCourseChapterDao()->batchCreate($newChapters);
                $newChapters = $this->getCourseChapterDao()->search(['copyIds' => array_column($newChapters, 'copyId'), 'courseIds' => array_column($newChapters, 'courseId')], [], 0, PHP_INT_MAX, ['id', 'courseId', 'copyId']);
                $newChapters = ArrayToolkit::groupIndex($newChapters, 'courseId', 'copyId');
            }
            // 创建任务
            $originCourseActivities = $originActivities[$copyCourse['parentId']] ?? [];
            $copyCourseActivities = $copyActivities[$copyCourse['id']] ?? [];
            $newActivityIds = array_diff(array_keys($originCourseActivities), array_keys($copyCourseActivities));
            $newActivities = [];
            foreach ($newActivityIds as $newActivityId) {
                $newActivity = $originCourseActivities[$newActivityId];
                $newActivity['copyId'] = $newActivity['id'];
                unset($newActivity['id']);
                $newActivity['fromCourseId'] = $copyCourse['id'];
                $newActivity['fromCourseSetId'] = $copyCourse['courseSetId'];
                $newActivities[] = $newActivity;
                $output->writeln("<info>待创建的任务activity: {$newActivityId}</info>");
            }
            if ($newActivities && $real) {
                $this->getActivityDao()->batchCreate($newActivities);
                $newActivities = $this->getActivityDao()->search(['copyIds' => array_column($newActivities, 'copyId'), 'courseIds' => array_column($newActivities, 'fromCourseId')], [], 0, PHP_INT_MAX, ['id', 'fromCourseId', 'copyId']);
                $newActivities = ArrayToolkit::groupIndex($newActivities, 'fromCourseId', 'copyId');
            }
            // 创建课时
            $toCopyTaskIds = array_diff($originTaskIds, $copyTaskIds);
            if ($toCopyTaskIds) {
                $toCopyTaskIds = implode(',', $toCopyTaskIds);
                $output->writeln("<info>待创建的课时: {$toCopyTaskIds}</info>");
            }
            $newTasks = [];
            foreach ($toCopyTaskIds as $toCopyTaskId) {
                $originTask = $originTasks[$copyCourse['parentId']][$toCopyTaskId];
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
                $task['courseId'] = $copyCourse['id'];
                if (isset($newChapters[$copyCourse['id']][$originTask['categoryId']])) {
                    $task['categoryId'] = $newChapters[$copyCourse['id']][$originTask['categoryId']]['id'];
                } else {
                    $task['categoryId'] = $copyChapters[$copyCourse['id']][$originTask['categoryId']]['id'];
                }
                if (isset($newActivities[$copyCourse['id']][$originTask['activityId']])) {
                    $task['activityId'] = $newActivities[$copyCourse['id']][$originTask['activityId']]['id'];
                } else {
                    $task['activityId'] = $copyActivities[$copyCourse['id']][$originTask['categoryId']]['id'];
                }
                $task['copyId'] = $toCopyTaskId;
                $task['fromCourseSetId'] = $copyCourse['courseSetId'];
                $task['createdUserId'] = $copyCourse['creator'];
                $newTasks[] = $task;
                $output->writeln("<info>待创建未同步的任务: {$task['title']}</info>");
            }
            if ($newTasks && $real) {
                $this->getTaskDao()->batchCreate($newTasks);
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
