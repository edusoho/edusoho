<?php

namespace Biz\Task\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\ActivityDao;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Dao\CourseMaterialDao;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\System\Constant\LogModule;
use Biz\Task\Dao\TaskDao;

class FixNotSyncTaskJob extends AbstractSyncJob
{
    public function execute()
    {
        $courseId = $this->findTaskNotSyncCourseId();
        if ($courseId) {
            $this->fixNotSyncTask($courseId);
            $this->registerFixNotSyncTaskJob();

            return;
        }
        $this->fixNotSyncTaskStatus();
    }

    private function findTaskNotSyncCourseId()
    {
        $courseIds = $this->biz['db']->fetchAll('
           SELECT DISTINCT(c.parentId) FROM course_v8 c
           JOIN (SELECT courseId, count(*) num FROM course_task GROUP BY courseId) sync ON c.id=sync.courseId
           JOIN (SELECT courseId, count(*) num FROM course_task GROUP BY courseId) origin ON c.parentId=origin.courseId
           WHERE sync.num!=origin.num AND c.locked=1 limit 1;
        ');

        return empty($courseIds) ? false : reset($courseIds);
    }

    private function fixNotSyncTask($courseId)
    {
        $tasks = $this->biz['db']->fetchAll("SELECT id, activityId FROM `course_task` WHERE courseId={$courseId}");
        $syncCourses = $this->biz['db']->fetchAll("SELECT id, courseSetId FROM `course_v8` WHERE locked=1 AND parentId={$courseId}");
        try {
            $this->fixNotSyncDeleteTask($tasks, $syncCourses);
            $this->fixNotSyncCreateTask($tasks, $syncCourses);
        } catch (\Exception $exception) {
            $this->innodbTrxLog($exception);
        }
    }

    private function fixNotSyncDeleteTask($tasks, $syncCourses)
    {
        $taskIdsStr = implode(',', array_column($tasks, 'id'));
        $syncCourseIdsStr = implode(',', array_column($syncCourses, 'id'));
        $toDeleteSyncTaskIds = $this->biz['db']->fetchAll("SELECT id FROM `course_task` WHERE courseId IN ({$syncCourseIdsStr}) AND copyId!=0 AND copyId NOT IN ({$taskIdsStr})");
        $this->getTaskService()->deleteTasks($toDeleteSyncTaskIds);
        $this->getLogService()->info(LogModule::COURSE, 'fix_delete_sync_task', '删除历史未同步删除课时成功', ['taskIds' => $toDeleteSyncTaskIds]);
    }

    private function fixNotSyncCreateTask($tasks, $syncCourses)
    {
        $taskIds = array_column($tasks, 'id');
        $taskIdsStr = implode(',', $taskIds);
        $syncCourseIds = array_column($syncCourses, 'id');
        $syncCourseIdsStr = implode(',', $syncCourseIds);
        $syncTasks = $this->biz['db']->fetchAll("SELECT courseId, copyId FROM `course_task` WHERE courseId IN ({$syncCourseIdsStr}) AND copyId IN ({$taskIdsStr})");
        $syncTasks = ArrayToolkit::group($syncTasks, 'copyId');
        $toFixTasks = [];
        foreach ($taskIds as $taskId) {
            if (empty($syncTasks[$taskId])) {
                $toFixTasks[$taskId] = $syncCourseIds;
                continue;
            }
            $toFixCourseIds = array_diff($syncCourseIds, array_column($syncTasks[$taskId], 'courseId'));
            if ($toFixCourseIds) {
                $toFixTasks[$taskId] = $toFixCourseIds;
            }
        }
        if (empty($toFixTasks)) {
            return;
        }
        $tasks = array_map(function ($task) use ($toFixTasks) {
            return !empty($toFixTasks[$task['id']]);
        }, $tasks);
        $this->syncCreate($toFixTasks, $tasks, $syncCourses);
        $this->getLogService()->info(LogModule::COURSE, 'fix_create_sync_task', '创建历史未同步创建课时成功', ['fixTasks' => $toFixTasks]);
    }

    private function syncCreate($toFixTasks, $tasks, $syncCourses)
    {
        if (empty($tasks)) {
            return;
        }
        $syncActivities = $this->createSyncActivities($toFixTasks, $tasks, $syncCourses);
        $this->createSyncTasks($toFixTasks, $syncCourses, $syncActivities);
    }

    private function createSyncActivities($toFixTasks, $tasks, $syncCourses)
    {
        $activityIds = array_column($tasks, 'activityId');
        $activityIdsStr = implode(',', $activityIds);
        $activities = $this->biz['db']->fetchAll("SELECT * FROM `activity` WHERE id IN ({$activityIdsStr})");
        $activities = array_column($activities, null, 'id');
        $tasks = array_column($tasks, null, 'id');
        $syncCourses = array_column($syncCourses, null, 'id');
        $createdActivities = $this->getActivityDao()->findByCopyIdsAndCourseIds($activityIds, array_column($syncCourses, 'id'));
        $createdActivities = ArrayToolkit::groupIndex($createdActivities, 'copyId', 'fromCourseId');
        $syncActivities = [];
        foreach ($toFixTasks as $taskId => $toFixCourseIds) {
            $activity = $activities[$tasks[$taskId]['activityId']];
            $syncActivity = ArrayToolkit::parts($activity, ['title', 'remark', 'mediaType', 'content', 'length', 'fromUserId', 'startTime', 'endTime', 'finishType', 'finishData']);
            $syncActivity['copyId'] = $activity['id'];
            foreach ($toFixCourseIds as $toFixCourseId) {
                if (!empty($createdActivities[$activity['id']][$toFixCourseId])) {
                    continue;
                }
                $syncActivity['fromCourseId'] = $toFixCourseId;
                $syncActivity['fromCourseSetId'] = $syncCourses[$toFixCourseId]['courseSetId'];
                $ext = $this->biz["activity_type.{$activity['mediaType']}"]->copy($activity, [
                    'refLiveroom' => 1, 'newActivity' => $syncActivity, 'isCopy' => 1, 'isSync' => 1,
                ]);
                if (!empty($ext)) {
                    $syncActivity['mediaId'] = $ext['id'];
                }
                $syncActivities[] = $syncActivity;
            }
        }
        $this->getActivityDao()->batchCreate($syncActivities);
        $syncActivities = $this->getActivityDao()->findByCopyIdsAndCourseIds($activityIds, array_column($syncCourses, 'id'));
        $this->createSyncMaterials($activityIds, $syncActivities);

        return $syncActivities;
    }

    private function createSyncMaterials($activityIds, $syncActivities)
    {
        $materials = $this->getMaterialDao()->search(['lessonIds' => $activityIds], [], 0, PHP_INT_MAX);
        if (empty($materials)) {
            return;
        }
        $createdMaterials = $this->getMaterialDao()->search(['lessonIds' => array_column($syncActivities, 'id'), 'copyIds' => array_column($materials, 'id')], [], 0, PHP_INT_MAX, ['copyId', 'lessonId']);
        $createdMaterials = ArrayToolkit::groupIndex($createdMaterials, 'copyId', 'lessonId');
        $syncActivities = ArrayToolkit::group($syncActivities, 'copyId');
        $syncMaterials = [];
        foreach ($materials as $material) {
            $syncMaterial = ArrayToolkit::parts($material, [
                'title',
                'description',
                'link',
                'fileId',
                'fileUri',
                'fileMime',
                'fileSize',
                'source',
                'userId',
                'type',
            ]);
            $syncMaterial['copyId'] = $material['id'];
            foreach ($syncActivities[$material['lessonId']] as $syncActivity) {
                if (!empty($createdMaterials[$material['id']][$syncActivity['id']])) {
                    continue;
                }
                $syncMaterial['courseSetId'] = $syncActivity['fromCourseSetId'];
                $syncMaterial['courseId'] = $syncActivity['fromCourseId'];
                $syncMaterial['lessonId'] = $syncActivity['id'];
                $syncMaterials[] = $syncMaterial;
            }
        }
        $this->getMaterialDao()->batchCreate($syncMaterials);
    }

    private function createSyncTasks($toFixTasks, $syncCourses, $syncActivities)
    {
        $syncCourseIds = array_column($syncCourses, 'id');
        $createdTasks = $this->getTaskDao()->findByCopyIdSAndLockedCourseIds(array_keys($toFixTasks), $syncCourseIds);
        $createdTasks = ArrayToolkit::groupIndex($createdTasks, 'copyId', 'courseId');
        $tasks = $this->getTaskDao()->findByIds(array_keys($toFixTasks));
        $tasks = array_column($tasks, null, 'id');
        $syncChapters = $this->getChapterDao()->findByCopyIdsAndLockedCourseIds(array_column($tasks, 'categoryId'), $syncCourseIds);
        $syncChapters = ArrayToolkit::groupIndex($syncChapters, 'copyId', 'courseId');
        $syncCourses = array_column($syncCourses, null, 'id');
        $syncActivities = ArrayToolkit::groupIndex($syncActivities, 'copyId', 'courseId');
        $syncTasks = [];
        foreach ($toFixTasks as $taskId => $toFixCourseIds) {
            $syncTask = ArrayToolkit::parts($tasks[$taskId], ['createdUserId', 'seq', 'title', 'isFree', 'isOptional', 'isLesson', 'startTime', 'endTime', 'number', 'mode', 'type', 'mediaSource', 'maxOnlineNum', 'status', 'length']);
            $syncTask['copyId'] = $taskId;
            foreach ($toFixCourseIds as $toFixCourseId) {
                if (!empty($createdTasks[$taskId][$toFixCourseId])) {
                    continue;
                }
                $syncTask['courseId'] = $toFixCourseId;
                $syncTask['fromCourseSetId'] = $syncCourses[$toFixCourseId]['courseSetId'];
                $syncTask['activityId'] = $syncActivities[$tasks[$taskId]['activityId']][$toFixCourseId];
                $syncTask['categoryId'] = $syncChapters[$tasks[$taskId]['categoryId']][$toFixCourseId]['id'];
                $syncTasks[] = $syncTask;
            }
        }
        $this->getTaskDao()->batchCreate($syncTasks);
    }

    private function fixNotSyncTaskStatus()
    {
        $this->biz['db']->exec('UPDATE `course_task` sync, `course_task` origin SET sync.`status`=origin.`status` WHERE sync.`copyId`!=0 AND sync.`copyId`=origin.`id` AND sync.`status`!=origin.`status`;');
    }

    private function registerFixNotSyncTaskJob()
    {
        $this->getSchedulerService()->register([
            'name' => 'FixNotSyncTaskJob_FixHistory',
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => time(),
            'misfire_policy' => 'executing',
            'class' => 'Biz\Task\Job\FixNotSyncTaskJob',
            'args' => [],
        ]);
    }

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }

    /**
     * @return CourseMaterialDao
     */
    private function getMaterialDao()
    {
        return $this->biz->dao('Course:CourseMaterialDao');
    }

    /**
     * @return TaskDao
     */
    private function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
    }

    /**
     * @return CourseChapterDao
     */
    private function getChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }
}
