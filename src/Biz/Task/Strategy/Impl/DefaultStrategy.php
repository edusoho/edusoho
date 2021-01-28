<?php

namespace Biz\Task\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Task\Strategy\BaseStrategy;
use Biz\Task\Strategy\CourseStrategy;
use Biz\Task\TaskException;
use Biz\Task\Visitor\CourseStrategyVisitorInterface;

class DefaultStrategy extends BaseStrategy implements CourseStrategy
{
    public function accept(CourseStrategyVisitorInterface $visitor)
    {
        $method = 'visit'.substr(strrchr(__CLASS__, '\\'), 1);

        return $visitor->$method($this);
    }

    public function canLearnTask($task)
    {
        return true;
    }

    protected function getFinishedTaskPerDay($course, $tasks)
    {
        $taskNum = $course['taskNum'];
        if ('days' == $course['expiryMode']) {
            $finishedTaskPerDay = empty($course['expiryDays']) ? false : $taskNum / $course['expiryDays'];
        } else {
            $diffDay = ($course['expiryEndDate'] - $course['expiryStartDate']) / (24 * 60 * 60);
            $finishedTaskPerDay = empty($diffDay) ? false : $taskNum / $diffDay;
        }

        return round($finishedTaskPerDay, 0);
    }

    public function getTasksListJsonData($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $tasks = $this->getTaskService()->findTasksFetchActivityByCourseId($courseId);
        $items = $this->prepareCourseItems($course['id'], $tasks);

        return array(
            'data' => array(
                'items' => $items,
            ),
            'template' => 'lesson-manage/default-list.html.twig',
        );
    }

    public function getTasksJsonData($task)
    {
        $course = $this->getCourseService()->getCourse($task['courseId']);
        $tasks = $this->getTaskService()->findTasksFetchActivityByChapterId($task['categoryId']);
        $lesson = $this->getChapterDao()->get($task['categoryId']);
        $lesson['tasks'] = $tasks;

        return array(
            'data' => array(
                'course' => $course,
                'lesson' => $lesson,
            ),
            'template' => 'lesson-manage/default/lesson.html.twig',
        );
    }

    public function createTask($field)
    {
        $this->validateTaskMode($field);
        if (isset($field['mode']) && 'lesson' != $field['mode']) {
            // 创建课时中的环节
            return $this->_createLessonLink($field);
        } else {
            // 创建课时
            return $this->_createLesson($field);
        }
    }

    public function updateTask($id, $fields)
    {
        $this->validateTaskMode($fields);
        $task = parent::updateTask($id, $fields);

        if ('lesson' == $task['mode']) {
            $this->getCourseService()->updateChapter(
                $task['courseId'],
                $task['categoryId'],
                array('title' => $task['title'])
            );
        }

        return $task;
    }

    protected function validateTaskMode($field)
    {
        if (!empty($field['mode']) && !in_array(
                $field['mode'],
                array('preparation', 'lesson', 'exercise', 'homework', 'extraClass')
            )
        ) {
            throw TaskException::ERROR_TASK_MODE();
        }
    }

    public function prepareCourseItems($courseId, $tasks, $limitNum = 0)
    {
        if ($limitNum) {
            $tasks = array_slice($tasks, 0, $limitNum);
        }
        $tasks = $this->sortTasks($tasks);

        $items = array();
        $chapters = $this->getChapterDao()->findChaptersByCourseId($courseId);
        foreach ($chapters as $chapter) {
            $chapter['itemType'] = 'chapter';
            $items["chapter-{$chapter['id']}"] = $chapter;
        }

        uasort(
            $items,
            function ($item1, $item2) {
                return $item1['seq'] > $item2['seq'];
            }
        );

        $taskCount = 1;
        foreach ($items as $key => $item) {
            if ($limitNum && $taskCount > $limitNum) {
                unset($items[$key]);
            }
            if ('lesson' !== $item['type']) {
                continue;
            }

            if (!empty($tasks[$item['id']])) {
                $items[$key]['tasks'] = $tasks[$item['id']];
                $taskCount += count($tasks[$item['id']]);
            } else {
                unset($items[$key]);
            }
        }

        return $items;
    }

    protected function sortTasks($tasks)
    {
        $tasks = ArrayToolkit::group($tasks, 'categoryId');
        $modes = array(
            'preparation' => 0,
            'lesson' => 1,
            'exercise' => 2,
            'homework' => 3,
            'extraClass' => 4,
        );

        foreach ($tasks as $key => $taskGroups) {
            uasort(
                $taskGroups,
                function ($item1, $item2) use ($modes) {
                    return $modes[$item1['mode']] > $modes[$item2['mode']];
                }
            );

            $tasks[$key] = $taskGroups;
        }

        return $tasks;
    }

    //发布课时中一组任务
    public function publishTask($task)
    {
        $tasks = $this->getTaskDao()->findByChapterId($task['categoryId']);
        foreach ($tasks as $task) {
            $this->getTaskDao()->update($task['id'], array('status' => 'published'));
        }
        $task['status'] = 'published';

        return $task;
    }

    //取消发布课时中一组任务
    public function unpublishTask($task)
    {
        return $this->getTaskDao()->update($task['id'], array('status' => 'unpublished'));
    }

    private function _createLesson($task)
    {
        /*$chapter = array(
            'courseId' => $task['fromCourseId'],
            'title' => $task['title'],
            'type' => 'lesson',
            'status' => 'create',
        );
        $chapter = $this->getCourseService()->createChapter($chapter);
        $task['categoryId'] = $chapter['id'];*/
        $task['mode'] = 'lesson';

        return parent::createTask($task);
    }

    private function _createLessonLink($task)
    {
        $lessonTask = $this->getTaskDao()->getByChapterIdAndMode($task['categoryId'], 'lesson');

        if (empty($lessonTask)) {
            throw TaskException::NOTFOUND_TASK();
        }

        $task['isOptional'] = $lessonTask['isOptional'];

        $task = parent::createTask($task);
        if ('published' == $lessonTask['status']) {
            $this->getTaskService()->publishTask($task['id']);
        }

        return $this->getTaskService()->getTask($task['id']);
    }

    protected function getTaskSeq($taskMode, $chapterSeq)
    {
        $taskModes = array('preparation' => 2, 'lesson' => 1, 'exercise' => 3, 'homework' => 4, 'extraClass' => 5);
        if (!array_key_exists($taskMode, $taskModes)) {
            throw TaskException::ERROR_TASK_MODE();
        }

        return $chapterSeq + $taskModes[$taskMode];
    }
}
