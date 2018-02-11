<?php

namespace Biz\Task\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Task\Strategy\BaseStrategy;
use Biz\Task\Strategy\CourseStrategy;
use Biz\Task\Visitor\CourseStrategyVisitorInterface;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

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

    public function getTasksTemplate()
    {
        return 'lesson-manage/normal-list.html.twig';
    }

    public function getTaskItemTemplate()
    {
        return 'lesson-manage/normal/lesson.html.twig';
    }

    public function getJsonTemplate($task)
    {
        if (!empty($task['categoryId'])) {
            $chapter = $this->getChapterDao()->get($task['categoryId']);
            if ('lesson' == $chapter['type']) {
                return 'lesson-manage/normal/tasks.html.twig';
            }
        }

        return 'lesson-manage/normal/lesson.html.twig';
    }

    public function deleteTask($task)
    {
        if (empty($task)) {
            return true;
        }

        try {
            $this->biz['db']->beginTransaction();

            $this->getTaskDao()->delete($task['id']);
            $this->getTaskResultService()->deleteUserTaskResultByTaskId($task['id']);
            $this->getActivityService()->deleteActivity($task['activityId']);

            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * 任务学习.
     *
     * @param  $task
     *
     * @throws NotFoundException
     *
     * @return bool
     */
    public function canLearnTask($task)
    {
        $course = $this->getCourseService()->getCourse($task['courseId']);

        //自由式学习 可以学习任意课时
        if ($course['learnMode'] == 'freeMode') {
            return true;
        }

        //选修任务不需要判断解锁条件
        if ($task['isOptional']) {
            return true;
        }

        if ($task['type'] == 'live') {
            return true;
        }

        if ($task['type'] == 'testpaper' && $task['startTime']) {
            return true;
        }

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);
        if ($taskResult['status'] == 'finish') {
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

    public function prepareCourseItems($courseId, $tasks, $limitNum)
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

    public function oldPrepareCourseItems($courseId, $tasks, $limitNum)
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
            if (strpos($key, 'task') !== false) {
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
        if (!empty($task['categoryId'])) {
            $chapter = $this->getCourseService()->getChapter($task['fromCourseId'], $task['categoryId']);
            if (empty($chapter)) {
                throw $this->createInvalidArgumentException('CategoryId Invalid');
            }
        } else {
            $chapter = array(
                'courseId' => $task['fromCourseId'],
                'title' => $task['title'],
                'type' => 'lesson',
                'status' => 'created',
            );
            $chapter = $this->getCourseService()->createChapter($chapter);
        }
        $task['categoryId'] = $chapter['id'];
        $task['status'] = $chapter['status'];

        return parent::createTask($task);
    }
}
