<?php

namespace Biz\Task\Copy;

use Biz\AbstractCopy;
use Biz\Activity\Dao\ActivityDao;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Task\Dao\TaskDao;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class CourseTaskCopy extends AbstractCopy
{
    public function preCopy($source, $options)
    {
        // TODO: Implement preCopy() method.
    }

    public function doCopy($source, $options)
    {
        $user = $this->biz['user'];
        $course = $options['originCourse'];
        $newCourse = $options['newCourse'];
        $newCourseSet = $options['newCourseSet'];
        $tasks = $this->getTaskDao()->findByCourseId($course['id']);

        $this->doChildrenProcess($source, $options);

        $chapters = $this->getChapterDao()->findChaptersByCourseId($newCourse['id']);

        $chaptersMap = ArrayToolkit::index($chapters, 'copyId');

        $activities = $this->getActivityDao()->findByCourseId($newCourse['id']);

        $activitiesMap = ArrayToolkit::index($activities, 'copyId');

        if (empty($tasks)) {
            return array();
        }

        $newTasks = array();
        $updateChapterIds = array();
        foreach ($tasks as $task) {
            $newTask = $this->partsFields($task);
            $newTask['courseId'] = $newCourse['id'];
            $newTask['fromCourseSetId'] = $newCourseSet['id'];
            if (!empty($chaptersMap[$task['categoryId']])) {
                $chapter = $chaptersMap[$task['categoryId']];
                $newTask['categoryId'] = $chapter['id'];
            }
            if ('live' == $task['type']) {
                //$newTask['status'] = 'create';
                $updateChapterIds[] = empty($chapter) ? 0 : $chapter['id'];
            }

            if (!empty($activitiesMap[$task['activityId']])) {
                $newTask['activityId'] = $activitiesMap[$task['activityId']]['id'];
            }

            $newTask['createdUserId'] = $user['id'];
            $newTask['copyId'] = $task['id'];
            $newTasks[] = $newTask;
        }

        if (!empty($newTasks)) {
            $this->getTaskDao()->batchCreate($newTasks);
            $this->updateChapter($newCourse['id'], $updateChapterIds);
        }
    }

    protected function doChildrenProcess($source, $options)
    {
        $childrenNodes = $this->getChildrenNodes();
        foreach ($childrenNodes as $childrenNode) {
            $CopyClass = $childrenNode['class'];
            $copyClass = new $CopyClass($this->biz, $childrenNode, isset($childrenNode['auto']) ? $childrenNode['auto'] : true);
            $copyClass->copy($source, $options);
        }
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
            'isLesson',
            'mediaSource',
            'status',
            'length',
        );
    }

    /**
     * [当有直播任务时，修改该课时及所包含的所有任务状态为未发布]
     *
     * @param [type] $courseId   [description]
     * @param [type] $chapterIds 包含直播任务的课时ids
     *
     * @return [type] [description]
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
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }
}
