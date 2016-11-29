<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 29/11/2016
 * Time: 14:52
 */

namespace Biz\Task\Strategy;


class BaseLearningStrategy
{
    public function __construct($biz)
    {
        $this->biz = $biz;
    }


    public function findCourseItems($courseId)
    {
        $items = array();
        $tasks = $this->getTaskService()->findUserTasksFetchActivityAndResultByCourseId($courseId);
        foreach ($tasks as $task) {
            $task['itemType']            = 'task';
            $items["task-{$task['id']}"] = $task;
        }

        $chapters = $this->getChapterDao()->findChaptersByCourseId($courseId);
        foreach ($chapters as $chapter) {
            $chapter['itemType']               = 'chapter';
            $items["chapter-{$chapter['id']}"] = $chapter;
        }

        uasort($items, function ($item1, $item2) {
            return $item1['seq'] > $item2['seq'];
        });

        return $items;
    }

    protected function getChapterDao()
    {
        return  $this->biz->dao('Course:CourseChapterDao');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    protected function getTaskDao()
    {
        return $this->biz->service('Task:TaskDao');
    }

    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

}