<?php

namespace Biz\Course\Component\Clones\Chain;

use Biz\Course\Component\Clones\AbstractClone;
use Biz\Task\Dao\TaskDao;

class CourseTaskClone extends AbstractClone
{
    protected function cloneEntity($source, $options)
    {
        return $this->cloneCourseTasks($source, $options);
    }

    private function cloneCourseTasks($source, $options)
    {
        $user = $this->biz['user'];
        $newCourse = $options['newCourse'];
        $newCourseSet = $options['newCourseSet'];
        $tasks = $this->getTaskDao()->findByCourseId($source['id']);

        $chaptersMap = $this->cloneCourseChapters($source, $options);

        if (empty($tasks)) {
            return array();
        }
        
        $activitiesMap = $this->cloneCourseActivities($source,$options);

        $newTasks = array();
        foreach ($tasks as $task){
            $newTask = $this->filterFields($task);
            $newTask['courseId'] = $newCourse['id'];
            $newTask['fromCourseSetId'] = $newCourseSet['id'];
            if(!empty($task['categoryId'])) {
                $chapter = $newChapter = $chaptersMap[$task['categoryId']];
                $newTask['categoryId'] = $chapter['id'];
            }

//            $newTask['activityId'] = $activitiesMap[$task['activityId']];
            $newTask['createdUserId'] = $user['id'];
            $newTasks[] = $newTask;

        }

        if(!empty($newTasks)) {
            $this->getTaskDao()->batchCreate($newTasks);
        }
    }
    
    private function cloneCourseActivities($source, $options)
    {
        $cloneActivities = new CourseActivityClone($this->biz);
        return $cloneActivities->clones($source,$options);

    }

    private function cloneCourseChapters($source, $options)
    {
        $cloneChapters = new CourseChapterClone($this->biz);

        return $cloneChapters->clones($source, $options);
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
}
