<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Course\Service\CourseService;

class CourseServiceImpl extends BaseService implements CourseService
{
    public function getCourseItems($courseId)
    {
        $items = array();

        $tasks = $this->getTaskService()->findTasksByCourseId($courseId);
        foreach ($tasks as $task) {
            $task['itemType']              = 'task';
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

    public function tryManageCourse($courseId) 
    {
        return true;
    }

    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    protected function getChapterDao()
    {
        return $this->createDao('Course:CourseChapterDao');
    }
}