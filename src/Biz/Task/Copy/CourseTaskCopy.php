<?php

namespace Biz\Task\Copy;

use Biz\AbstractCopy;
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
        $newCourse = $options['newCourse'];
        $newCourseSet = $options['newCourseSet'];
        $tasks = $this->getTaskDao()->findByCourseId($source['id']);
        if (empty($tasks)) {
            return array();
        }

        $this->doChildrenProcess($source,$options);


        $chapters = $this->getChapterDao()->findChaptersByCourseId($newCourse['id']);

        $chaptersMap = ArrayToolkit::index($chapters,'copyId');

        $activitiesMap = $this->cloneCourseActivities($source, $options);

        $newTasks = array();
        foreach ($tasks as $task) {
            $newTask = $this->partsFields($task);
            $newTask['courseId'] = $newCourse['id'];
            $newTask['fromCourseSetId'] = $newCourseSet['id'];
            if (!empty($task['categoryId'])) {
                $chapter = $newChapter = $chaptersMap[$task['categoryId']];
                $newTask['categoryId'] = $chapter['id'];
            }

            $newTask['activityId'] = $activitiesMap[$task['activityId']];
            $newTask['createdUserId'] = $user['id'];
            $newTasks[] = $newTask;
        }

        if (!empty($newTasks)) {
            $this->getTaskDao()->batchCreate($newTasks);
        }
    }

    protected function doChildrenProcess($source,$options)
    {

        $currentNode = $this->getCurrentNodeName();
        $copyChain = $this->getCopyChain();
        $childrenNodes = $this->getChildrenNodes($currentNode, $copyChain);
        foreach ($childrenNodes as $childrenNode) {
            $CopyClass = $childrenNode['class'];
            $copyClass = new $CopyClass($this->biz, $childrenNode);
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
}
