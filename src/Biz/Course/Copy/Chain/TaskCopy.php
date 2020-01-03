<?php

namespace Biz\Course\Copy\Chain;

use Biz\Task\Dao\TaskDao;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class TaskCopy extends AbstractEntityCopy
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
     * $config = $newCourse, $modeChange
     * isCopy 表示是否是班级复制
     */
    protected function copyEntity($source, $config = array())
    {
        $user = $this->biz['user'];
        $tasks = $this->getTaskService()->findTasksByCourseId($source['id']);

        $chapterMap = $this->doCopyChapters($source, $config);

        if (empty($tasks)) {
            return array();
        }

        $modeChange = $config['modeChange'];
        $newCourse = $config['newCourse'];
        $newCourseSetId = $newCourse['courseSetId'];

        $activityMap = $this->doCopyActivities($source, $config);

        //task order by seq
        usort($tasks, function ($t1, $t2) {
            return $t1['seq'] - $t2['seq'];
        });

        //sort tasks
        $num = 1;
        $newTasks = array();
        $updateChapterIds = array();
        foreach ($tasks as $task) {
            $newTask = $this->doCopyTask($task, $config['isCopy']);
            $newTask['courseId'] = $newCourse['id'];
            //number 代表任务的次序，默认教学计划 和 自由式与解锁式的设置方法不同
            //对于默认教学计划，跟lesson同级的五个任务拥有相同的number，
            //对于自由式和解锁式，则每个任务按照seq次序依次排列
            //因此，当从默认教学计划复制为自由式/解锁式的时候需要重新计算number
            if ($modeChange && !$newTask['isOptional']) {
                $newTask['number'] = $num++;
            }
            $newTask['fromCourseSetId'] = $newCourseSetId;
            if (!empty($task['categoryId'])) {
                $newChapter = $chapterMap[$task['categoryId']];
                //如果是从默认教学计划复制，则删除type=lesson的chapter，并将对应task的categoryId指向该chapter的父级
                /*if ($modeChange && $newChapter['type'] === 'lesson') {
                    $this->getChapterDao()->delete($newChapter['id']);
                    $newTask['mode'] = 'default';
                    unset($newTask['categoryId']);
                } else {*/
                $newTask['categoryId'] = $newChapter['id'];
                //}
            }

            if (!$config['isCopy'] && 'live' === $task['type']) {
                $updateChapterIds[] = empty($newChapter) ? 0 : $newChapter['id'];
            }

            $newTask['activityId'] = $activityMap[$task['activityId']];
            $newTask['createdUserId'] = $user['id'];
            $newTasks[] = $newTask;
        }

        $this->getTaskService()->batchCreateTasks($newTasks);

        $this->updateChapter($newCourse['id'], $updateChapterIds);

        return $this->getTaskService()->findTasksByCourseId($newCourse['id']);
    }

    private function doCopyChapters($source, $config)
    {
        $chapterCopy = new ChapterCopy($this->biz);

        return $chapterCopy->copy($source, $config);
    }

    private function doCopyActivities($source, $config)
    {
        $activityCopy = new ActivityCopy($this->biz);

        return $activityCopy->copy($source, $config);
    }

    protected function getFields()
    {
        return array(
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
        );
    }

    private function doCopyTask($task, $isCopy)
    {
        $new = $this->filterFields($task);

        $new['copyId'] = $isCopy ? $task['id'] : 0;

        /*if (!$isCopy && $task['type'] === 'live') {
            $new['status'] = 'create';
        }*/

        return $new;
    }

    /**
     * [当有直播任务时，修改该课时及所包含的所有任务状态为未发布]
     *
     * @param [type] $courseId   [description]
     * @param [type] $chapterIds 包含直播任务的课时ids
     */
    protected function updateChapter($courseId, $chapterIds)
    {
        if (empty($chapterIds)) {
            return;
        }

        $chapterBatchHelper = new BatchUpdateHelper($this->getChapterDao());
        foreach ($chapterIds as $chapterId) {
            $fields = array('status' => 'create');
            $chapterBatchHelper->add('id', $chapterId, $fields);
        }

        $chapterBatchHelper->flush();
        $this->getTaskDao()->update(array('courseId' => $courseId, 'categoryIds' => $chapterIds), array('status' => 'create'));
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
    }

    /**
     * @return CourseChapterDao
     */
    protected function getChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }
}
