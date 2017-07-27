<?php

namespace Biz\Task\Copy;

use Biz\AbstractCopy;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Task\Dao\TaskDao;
use Codeages\Biz\Framework\Util\ArrayToolkit;

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
        foreach ($tasks as $task) {
            $newTask = $this->partsFields($task);
            $newTask['courseId'] = $newCourse['id'];
            $newTask['fromCourseSetId'] = $newCourseSet['id'];
            if (!empty($chaptersMap[$task['categoryId']])) {
                $chapter = $newChapter = $chaptersMap[$task['categoryId']];
                $newTask['categoryId'] = $chapter['id'];
            }
            if ($task['type'] == 'live') {
                $newTask['status'] = 'create';
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
            'mediaSource',
            'status',
            'length',
        );
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
