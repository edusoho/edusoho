<?php

namespace Biz\S2B2C\Sync\Component;

use AppBundle\Common\ArrayToolkit;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;

class TaskSync extends AbstractEntitySync
{
    /**
     * 复制链说明：
     * Task 任务信息
     * - Chapter 多级章节信息
     * - Activity 活动信息
     *   - ActivityConfig 活动自定义信息
     *   - Material 关联到activity的Material
     *   - Testpaper 关联到Activity的testpaper.
     */

    /*
     * 这里同时处理task和chapter
     * $source = $originalCourse
     * $config = $newCourse
     */
    protected function syncEntity($source, $config = [])
    {
        $this->logger->info('[syncEntity] 开始同步章节');
        $chapterMap = $this->doSyncChapters($source, $config);
        $this->logger->info('[syncEntity] 章节同步完成');

        $tasks = $source['taskList'];
        if (empty($tasks)) {
            return [];
        }
        $this->logger->info('[syncEntity] 开始同步课程上传资源');
        $newUploadFiles = $this->doSyncUploadFiles($source, $config);
        $this->logger->info('[syncEntity] 同步课程上传资源完成');

        $config['newUploadFiles'] = $newUploadFiles;

        $this->logger->info('[syncEntity] 开始同步课程题库数据');
        $questionMap = $this->doSyncQuestions($source, $config);
        $this->logger->info('[syncEntity] 同步课程题库数据完成');

        $this->logger->info('[syncEntity] 开始同步课程教学活活动数据');
        $activityMap = $this->doSyncActivities($source, $config);
        $this->logger->info('[syncEntity] 同步课程教学活活动数据完成');

        //task ordered by seq
        usort($tasks, function ($t1, $t2) {
            return $t1['seq'] - $t2['seq'];
        });

        //sort tasks
        $newCourse = $config['newCourse'];
        $newCourseSetId = $newCourse['courseSetId'];
        $newTasks = [];
        $user = $this->biz['user'];
        foreach ($tasks as $task) {
            $newTask = $this->filterTaskFields($task);
            $newTask['courseId'] = $newCourse['id'];
            $newTask['fromCourseSetId'] = $newCourseSetId;
            if (!empty($task['categoryId'])) {
                $newChapter = $chapterMap[$task['categoryId']];
                $newTask['categoryId'] = $newChapter['id'];
            }

            $newTask['activityId'] = $activityMap[$task['activityId']];
            $newTask['createdUserId'] = $user['id'];
            $newTasks[] = $newTask;
        }

        $this->logger->info('[syncEntity] 开始同步课时任务信息');
        $this->getTaskService()->batchCreateTasks($newTasks);
        $this->logger->info('[syncEntity] 同步课时任务信息完成');

        return $this->getTaskService()->findTasksByCourseId($newCourse['id']);
    }

    private function doSyncChapters($source, $config)
    {
        $chapterSync = new ChapterSync($this->biz);

        return $chapterSync->sync($source, $config);
    }

    private function doSyncUploadFiles($source, $config)
    {
        $chapterSync = new UploadFileSync($this->biz);

        return $chapterSync->sync($source, $config);
    }

    private function doSyncQuestions($source, $config)
    {
        $questionSync = new CourseSetQuestionSync($this->biz);

        return $questionSync->sync($source, $config);
    }

    private function doSyncActivities($source, $config)
    {
        $activitySync = new ActivitySync($this->biz);

        return $activitySync->sync($source, $config);
    }

    protected function updateEntityToLastedVersion($source, $config = [])
    {
        $this->logger->info('[updateEntityToLastedVersion] 开始更新课程章节数据');
        $chapterMap = $this->doUpdateChapters($source, $config);
        $this->logger->info('[updateEntityToLastedVersion] 更新课程章节数据完成');
        $newCourse = $config['newCourse'];

        $tasks = $source['taskList'];
        $exitTasks = $this->getTaskDao()->search([
            'fromCourseSetId' => $newCourse['courseSetId'],
            'courseId' => $newCourse['id'],
        ], [], 0, PHP_INT_MAX);
        $exitTasks = ArrayToolkit::index($exitTasks, 'syncId');
        if (empty($tasks)) {
            $this->logger->info('[updateEntityToLastedVersion] 新版本中不存在任务，开始对现有任务进行删除');
            foreach ($exitTasks as $exitTask) {
                $this->getTaskService()->deleteTask($exitTask['id']);
            }
            $this->logger->info('[updateEntityToLastedVersion] 现有任务删除成功，更新版本完成');

            return [];
        }
        $newUploadFiles = $this->doUpdateUploadFiles($source, $config);

        $config['newUploadFiles'] = $newUploadFiles;
        $this->logger->info('[updateEntityToLastedVersion] 开始更新课程题库数据');
        $questionMap = $this->doUpdateQuestions($source, $config);
        $this->logger->info('[updateEntityToLastedVersion] 更新课程题库数据完成');

        $this->logger->info('[updateEntityToLastedVersion] 开始更新课程教学计划数据');
        $activityMap = $this->doUpdateActivities($source, $config);
        $this->logger->info('[updateEntityToLastedVersion] 更新课程教学计划数据完成');

        //task ordered by seq
        usort($tasks, function ($t1, $t2) {
            return $t1['seq'] - $t2['seq'];
        });

        $this->logger->info('[updateEntityToLastedVersion] 优先更新已存在的课时任务信息,tasks');
        $user = $this->biz['user'];
        $newTasks = [];
        foreach ($tasks as $task) {
            $newTask = $this->filterTaskFields($task);
            $newTask['courseId'] = $newCourse['id'];
            $newTask['fromCourseSetId'] = $newCourse['courseSetId'];
            if (!empty($task['categoryId'])) {
                $newChapter = $chapterMap[$task['categoryId']];
                $newTask['categoryId'] = $newChapter['id'];
            }

            $newTask['activityId'] = $activityMap[$task['activityId']];
            $newTask['createdUserId'] = $user['id'];
            if (!empty($exitTasks[$newTask['syncId']])) {
                $this->getTaskDao()->update($exitTasks[$newTask['syncId']]['id'], $newTask);
                continue;
            }

            $newTasks[] = $newTask;
        }
        $this->logger->info('[updateEntityToLastedVersion] 更新已存在的课时任务信息完成');

        $this->logger->info('[updateEntityToLastedVersion] 开始创建新增课时任务信息,tasks');
        $this->getTaskService()->batchCreateTasks($newTasks);
        $this->logger->info('[updateEntityToLastedVersion] 创建新增课时任务信息完成');

        $needDeleteTaskSyncIds = array_values(array_diff(array_keys($exitTasks), ArrayToolKit::column($tasks, 'id')));

        if (!empty($exitTasks) && !empty($needDeleteTaskSyncIds)) {
            $this->logger->info('[updateEntityToLastedVersion] 开始删除已经不存在的课时任务,tasks');
            $needDeleteTasks = $this->getTaskDao()->search([
                'fromCourseSetId' => $newCourse['courseSetId'],
                'courseId' => $newCourse['id'],
                'syncIds' => $needDeleteTaskSyncIds,
            ], [], 0, PHP_INT_MAX);

            foreach ($needDeleteTasks as $needDeleteTask) {
                $this->getTaskService()->deleteTask($needDeleteTask['id']);
            }
            $this->logger->info('[updateEntityToLastedVersion] 删除不存在的课时任务完成');
        }
        $this->logger->info('[updateEntityToLastedVersion] 更新课程版本完成');

        return $this->getTaskService()->findTasksByCourseId($newCourse['id']);
    }

    private function doUpdateChapters($source, $config)
    {
        $chapterSync = new ChapterSync($this->biz);

        return $chapterSync->updateEntityToLastedVersion($source, $config);
    }

    private function doUpdateUploadFiles($source, $config)
    {
        $chapterSync = new UploadFileSync($this->biz);

        return $chapterSync->updateEntityToLastedVersion($source, $config);
    }

    private function doUpdateQuestions($source, $config)
    {
        $questionSync = new CourseSetQuestionSync($this->biz);

        return $questionSync->updateEntityToLastedVersion($source, $config);
    }

    private function doUpdateActivities($source, $config)
    {
        $activitySync = new ActivitySync($this->biz);

        return $activitySync->updateEntityToLastedVersion($source, $config);
    }

    protected function getFields()
    {
        return [
            'seq',
            'activityId',
            'categoryId',
            'title',
            'isFree',
            'isOptional',
            'startTime',
            'endTime',
            'mode',
            'number',
            'type',
            'mediaSource',
            'status',
            'length',
            'isLesson',
        ];
    }

    private function filterTaskFields($task)
    {
        $new = $this->filterFields($task);
        $new['copyId'] = 0;
        $new['syncId'] = $task['id'];

        return $new;
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
    }
}
