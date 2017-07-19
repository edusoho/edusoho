<?php

namespace Biz\Task\Job;

use AppBundle\Common\ExceptionPrintingToolkit;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\Common\Logger;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseJobDao;
use Biz\Course\Dao\LearningDataAnalysisDao;
use Biz\Course\Service\MemberService;
use Biz\System\Service\LogService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Dao\BatchCreateHelper;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CourseTaskCreateSyncJob extends AbstractJob
{
    public function execute()
    {
        try {

            $task = $this->getTaskService()->getTask($this->args['taskId']);
            $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($task['courseId'], 1);

            $activity = $this->getActivityDao()->get($task['activityId']);

            $taskHelper = new BatchCreateHelper($this->getTaskDao());
            foreach ($copiedCourses as $cc) {
                $newActivity = $this->createActivity($activity, $cc);

                $newTask = array(
                    'courseId' => $cc['id'],
                    'fromCourseSetId' => $cc['courseSetId'],
                    'createdUserId' => $task['createdUserId'],
                    'seq' => $task['seq'],
                    'categoryId' => $task['categoryId'],
                    'activityId' => $newActivity['id'],
                    'title' => $task['title'],
                    'isFree' => $task['isFree'],
                    'isOptional' => $task['isOptional'],
                    'startTime' => $task['startTime'],
                    'endTime' => $task['endTime'],
                    'number' => $task['number'],
                    'mode' => $task['mode'],
                    'type' => $task['type'],
                    'mediaSource' => $task['mediaSource'],
                    'copyId' => $task['id'],
                    'maxOnlineNum' => $task['maxOnlineNum'],
                    'status' => $task['status'],
                );

                if (!empty($task['mode'])) {
                    $newChapter = $this->getChapterDao()->getByCopyIdAndLockedCourseId($task['categoryId'], $cc['id']);
                    $newTask['categoryId'] = $newChapter['id'];
                }

                $taskHelper->add($newTask);
            }

            $taskHelper->flush();

            //$this->getLogService()->info(Logger::COURSE, Logger::ACTION_REFRESH_LEARNING_PROGRESS, '刷新学习进度的定时任务执行成功', $courseIds);
        } catch (\Exception $e) {
            //$this->getLogService()->error(Logger::COURSE, Logger::ACTION_REFRESH_LEARNING_PROGRESS, '刷新学习进度的定时任务执行失败', ExceptionPrintingToolkit::printTraceAsArray($e));
        }
    }

    private function createActivity($activity, $copiedCourse)
    {
        //create testpaper&questions if ref exists
        $testpaper = $this->syncTestpaper($activity, $copiedCourse);

        $testId = empty($testpaper) ? 0 : $testpaper['id'];

        $newActivity = array(
            'title' => $activity['title'],
            'remark' => $activity['remark'],
            'mediaType' => $activity['mediaType'],
            'content' => $activity['content'],
            'length' => $activity['length'],
            'fromCourseId' => $copiedCourse['id'],
            'fromCourseSetId' => $copiedCourse['courseSetId'],
            'fromUserId' => $activity['fromUserId'],
            'startTime' => $activity['startTime'],
            'endTime' => $activity['endTime'],
            'copyId' => $activity['id'],
        );

        $ext = $this->getActivityConfig($activity['mediaType'])->copy($activity, array(
            'testId' => $testId, 'refLiveroom' => 1, 'newActivity' => $newActivity, 'isCopy' => 1,
        ));

        if (!empty($ext)) {
            $newActivity['mediaId'] = $ext['id'];
        }

        $newActivity = $this->getActivityDao()->create($newActivity);

        //create materials if exists
        $this->createMaterials($newActivity, $activity, $copiedCourse);

        return $newActivity;
    }

    private function syncTestpaper($activity, $copiedCourse)
    {
        if ($activity['mediaType'] != 'testpaper') {
            return array();
        }

        $testpaperCopy = new ActivityTestpaperCopy($this->getBiz());

        return $testpaperCopy->copy($activity, array(
            'newCourseSetId' => $copiedCourse['courseSetId'],
            'newCourseId' => $copiedCourse['id'],
            'isCopy' => 1,
        ));
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return CourseDao
     */
    private function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }

    /**
     * @return CourseChapterDao
     */
    private function getChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }

    /**
     * @return TaskDao
     */
    private function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
    }

    /**
     * @param  $type
     *
     * @return Activity
     */
    private function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }
}
