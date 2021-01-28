<?php

namespace Biz\Task\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Task\Strategy\BaseStrategy;
use Biz\Task\Strategy\CourseStrategy;
use Biz\Task\TaskException;
use Biz\Task\Visitor\CourseStrategyVisitorInterface;

class NormalStrategy extends BaseStrategy implements CourseStrategy
{
    public function accept(CourseStrategyVisitorInterface $visitor)
    {
        $method = 'visit'.substr(strrchr(__CLASS__, '\\'), 1);

        return $visitor->$method($this);
    }

    public function createTask($field)
    {
        $task = $this->_createLesson($field);
        $task['activity'] = $this->getActivityService()->getActivity($task['activityId'], $fetchMedia = true);

        return $task;
    }

    public function updateTask($id, $fields)
    {
        $task = parent::updateTask($id, $fields);

        if ($task['isLesson']) {
            $this->getCourseService()->updateChapter(
                $task['courseId'],
                $task['categoryId'],
                array('title' => $task['title'])
            );
        }

        return $task;
    }

    public function getTasksListJsonData($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $tasks = $this->getTaskService()->findTasksFetchActivityByCourseId($courseId);
        $items = $this->getTasksAndChapters($course['id'], $tasks);

        return array(
            'data' => array(
                'items' => $items,
            ),
            'template' => 'lesson-manage/normal-list.html.twig',
        );
    }

    public function getTasksJsonData($task)
    {
        $course = $this->getCourseService()->getCourse($task['courseId']);
        $taskNum = $this->getTaskService()->countTasksByChpaterId($task['categoryId']);
        $chapter = $this->getChapterDao()->get($task['categoryId']);
        $task['activity'] = $this->getActivityService()->getActivity($task['activityId'], $fetchMedia = true);
        $tasks = array($task);
        if (1 == $taskNum) {
            $template = 'lesson-manage/normal/lesson.html.twig';
        } elseif ($task['isLesson']) {
            $template = 'lesson-manage/normal/lesson.html.twig';
            $tasks = $this->getTaskService()->findTasksFetchActivityByChapterId($task['categoryId']);
        } else {
            $template = 'lesson-manage/normal/tasks.html.twig';
        }
        $chapter['tasks'] = $tasks;

        return array(
            'data' => array(
                'course' => $course,
                'lesson' => $chapter,
                'tasks' => $tasks,
            ),
            'template' => $template,
        );
    }

    /**
     * 任务学习.
     *
     * @param  $task
     *
     * @return bool
     */
    public function canLearnTask($task)
    {
        $course = $this->getCourseService()->getCourse($task['courseId']);

        //自由式学习 可以学习任意课时
        if ('freeMode' == $course['learnMode']) {
            return true;
        }

        //选修任务不需要判断解锁条件
        if ($task['isOptional']) {
            return true;
        }

        if ('live' == $task['type']) {
            return true;
        }

        if ('testpaper' == $task['type'] && $task['startTime']) {
            return true;
        }

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);
        if ('finish' == $taskResult['status']) {
            return true;
        }

        //取得下一个发布的课时
        $conditions = array(
            'courseId' => $task['courseId'],
            'seq_LT' => $task['seq'],
            'status' => 'published',
        );

        $count = $this->getTaskDao()->count($conditions);
        $preTasks = $this->getTaskDao()->search($conditions, array('seq' => 'DESC'), 0, $count);

        if (empty($preTasks)) {
            return true;
        }

        $taskIds = ArrayToolkit::column($preTasks, 'id');

        $taskResults = $this->getTaskResultService()->findUserTaskResultsByTaskIds($taskIds);

        $taskResults = ArrayToolkit::index($taskResults, 'courseTaskId');
        array_walk(
            $preTasks,
            function (&$task) use ($taskResults) {
                $task['result'] = isset($taskResults[$task['id']]) ? $taskResults[$task['id']] : null;
            }
        );

        return $this->getTaskService()->isPreTasksIsFinished($preTasks);
    }

    // 获得章，节，课时，任务
    // return  array(
    //     'chapter',
    //     'unit',
    //     'lesson' => array(
    //         'task'
    //     )
    // )
    protected function getTasksAndChapters($courseId, $tasks)
    {
        $items = array();
        uasort(
            $tasks,
            function ($item1, $item2) {
                return $item1['seq'] > $item2['seq'];
            }
        );
        $tasks = ArrayToolkit::group($tasks, 'categoryId');

        $chapters = $this->getChapterDao()->findChaptersByCourseId($courseId);
        uasort(
            $chapters,
            function ($item1, $item2) {
                return $item1['seq'] > $item2['seq'];
            }
        );
        foreach ($chapters as $index => $chapter) {
            $chapterId = $chapter['id'];
            if (!empty($tasks[$chapterId])) {
                $chapter['tasks'] = $tasks[$chapterId];
            }
            $items[] = $chapter;
        }

        return $items;
    }

    public function prepareCourseItems($courseId, $tasks, $limitNum)
    {
        $items = array();
        foreach ($tasks as $task) {
            $task['itemType'] = 'task';
            $items["task-{$task['id']}"] = $task;
        }

        $chapters = $this->getChapterDao()->findChaptersByCourseId($courseId);
        foreach ($chapters as $index => $chapter) {
            $chapter['itemType'] = 'chapter';
            $items["chapter-{$chapter['id']}"] = $chapter;
        }

        uasort(
            $items,
            function ($item1, $item2) {
                return $item1['seq'] > $item2['seq'];
            }
        );

        if (empty($limitNum)) {
            return $items;
        }

        $taskCount = 0;
        foreach ($items as $key => $item) {
            if (false !== strpos($key, 'task')) {
                ++$taskCount;
            }
            if ($taskCount > $limitNum) {
                unset($items[$key]);
            }
        }

        return $items;
    }

    public function publishTask($task)
    {
        return $this->getTaskDao()->update($task['id'], array('status' => 'published'));
    }

    public function unpublishTask($task)
    {
        return $this->getTaskDao()->update($task['id'], array('status' => 'unpublished'));
    }

    private function _createLesson($task)
    {
        $chapter = $this->getCourseService()->getChapter($task['fromCourseId'], $task['categoryId']);
        if (empty($chapter) || 'lesson' != $chapter['type']) {
            throw TaskException::CATEGORYID_INVALID();
        }

        $task['status'] = $chapter['status'];
        $task['isOptional'] = $chapter['isOptional'];

        return parent::createTask($task);
    }
}
