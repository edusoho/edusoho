<?php

namespace Biz\Task\Visitor;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Util\TaskItemNumUtils;
use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\Impl\DefaultStrategy;
use Biz\Task\Strategy\Impl\NormalStrategy;
use Codeages\Biz\Framework\Context\Biz;

class CourseItemPagingVisitor implements CourseStrategyVisitorInterface
{
    /**
     * @var Biz
     */
    private $biz;

    private $courseId;

    private $paging = array(
        'direction' => 'down',
        'limit' => 25,
        'offsetSeq' => 1,
        'offsetTaskId' => 0,
    );

    public function __construct(Biz $biz, $courseId, $paging)
    {
        $this->biz = $biz;
        $this->courseId = $courseId;

        $this->paging = array_merge($this->paging, $paging);
    }

    public function visitDefaultStrategy(DefaultStrategy $defaultStrategy)
    {
        return $this->startPaging();
    }

    public function visitNormalStrategy(NormalStrategy $normalStrategy)
    {
        return $this->startPaging();
    }

    public function startPaging()
    {
        $items = $this->findItems();

        /*
            key 为 lessonId, value 为 array(
                'lesson' => $lesson,
                'isSingleTaskLesson' => true/false,
                'taskId' => $taskId, // 单任务课时才有用
            )
        */
        $lessonInfos = array();
        foreach ($items as $key => &$item) {
            if ('chapter' == $item['type'] || 'unit' == $item['type']) {
                $item['itemType'] = $item['type'];
            } elseif ('lesson' == $item['type']) {
                $item['itemType'] = $item['type'];
                if (isset($lessonInfos[$item['id']])) {
                    $lessonInfos[$item['id']]['lesson'] = $item;
                } else {
                    $lessonInfos[$item['id']] = array('lesson' => $item);
                }
            } else {
                $item['itemType'] = 'task';
                $lessonId = $item['categoryId'];
                if (!isset($lessonInfos[$lessonId])) {
                    $lessonInfos[$lessonId] = array();
                }
                if (isset($lessonInfos[$lessonId]['isSingleTaskLesson'])) {
                    $lessonInfos[$lessonId]['isSingleTaskLesson'] = false;
                    if ($item['isLesson']) {
                        $lessonInfos[$lessonId]['taskId'] = $item['id'];
                    }
                } else {
                    $lessonInfos[$lessonId]['isSingleTaskLesson'] = true;
                    $lessonInfos[$lessonId]['taskId'] = $item['id'];
                }
            }
        }

        foreach ($items as $key => &$item) {
            if ('task' == $item['itemType']) {
                $lessonId = $item['categoryId'];
                $lessonInfo = $lessonInfos[$lessonId];
                if (!empty($lessonInfo['isSingleTaskLesson']) || $item['isLesson']) {
                    $currentLesson = $lessonInfo['lesson'];
                    $item['isSingleTaskLesson'] = true;
                    $item['seq'] = $currentLesson['seq'];
                    $item['number'] = $currentLesson['number'];
                    $item['title'] = $currentLesson['title'];
                    $item['published_number'] = $currentLesson['published_number'];
                } else {
                    $item['isSingleTaskLesson'] = false;
                }
            } elseif ('lesson' == $item['itemType']) {
                unset($items[$key]);
            }
        }

        uasort($items, function ($item1, $item2) {
            return $item1['seq'] > $item2['seq'];
        });

        $items = TaskItemNumUtils::resetNum($items);

        return array(array_values($items), $this->getNextOffsetSeq($items));
    }

    private function findItems()
    {
        if ($this->paging['offsetTaskId']) {
            list($chapters, $tasks) = $this->findItemsByTaskOffsetId();
        } else {
            $conditions = $this->getConditions();
            $chapters = $this->getChapterDao()->search(
                $conditions,
                array(),
                0,
                $this->paging['limit']
            );

            $tasks = $this->getTaskDao()->search(
                $conditions,
                array(),
                0,
                $this->paging['limit']
            );
        }

        $this->wrapTask($tasks);

        $items = array_merge($chapters, $tasks);

        return $items;
    }

    private function findItemsByTaskOffsetId()
    {
        $task = $this->getTaskDao()->get($this->paging['offsetTaskId']);

        $downLimit = $this->paging['limit'] / 2;
        $upLimit = $this->paging['limit'] - $downLimit - 1;
        $upConditions = array(
            'courseId' => $this->courseId,
            'seq_LT' => $task['seq'],
            'seq_GTE' => $task['seq'] - $upLimit,
        );

        $downConditions = array(
            'courseId' => $this->courseId,
            'seq_GT' => $task['seq'],
            'seq_LTE' => $task['seq'] + $downLimit,
        );

        if ($this->isHiddenUnpublishTasks()) {
            $upConditions['status'] = 'published';
            $downConditions['status'] = 'published';
        }

        $upChapters = $this->getChapterDao()->search(
            $upConditions,
            array(),
            0,
            $upLimit
        );

        $downChapters = $this->getChapterDao()->search(
            $downConditions,
            array(),
            0,
            $downLimit
        );

        $upTasks = $this->getTaskDao()->search(
            $upConditions,
            array(),
            0,
            $upLimit
        );

        $downTasks = $this->getTaskDao()->search(
            $downConditions,
            array(),
            0,
            $downLimit
        );

        $tasks = array_merge(array($task), $upTasks, $downTasks);

        $chapters = array_merge($upChapters, $downChapters);

        return array($chapters, $tasks);
    }

    private function getNextOffsetSeq($items)
    {
        if ($items) {
            $lastOne = end($items);
            $hasChapterCount = $this->getChapterDao()->count(array(
                'courseId' => $this->courseId,
                'seq_GT' => $lastOne['seq'],
            ));

            $hasTaskCount = $this->getTaskDao()->count(array(
                'courseId' => $this->courseId,
                'seq_GT' => $lastOne['seq'],
            ));

            return $hasChapterCount || $hasTaskCount ? ($lastOne['seq'] + 1) : null;
        }

        return null;
    }

    private function wrapTask(&$tasks)
    {
        if (empty($tasks)) {
            return;
        }

        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities = $this->getActivityService()->findActivities($activityIds, true, 0);
        $activities = ArrayToolkit::index($activities, 'id');

        foreach ($tasks as &$task) {
            $task['activity'] = $activities[$task['activityId']];
        }

        $tasks = $this->getTaskService()->wrapTaskResultToTasks($this->courseId, $tasks);
    }

    private function getConditions()
    {
        $conditions = array(
            'courseId' => $this->courseId,
        );

        if ($this->isHiddenUnpublishTasks()) {
            $conditions['status'] = 'published';
        }

        // if ('down' == $this->paging['direction']) {
        //     $conditions['seq_GTE'] = $this->paging['offsetSeq'];
        //     $conditions['seq_LTE'] = $this->paging['offsetSeq'] + $this->paging['limit'] - 1;
        // }

        // if ('up' == $this->paging['direction']) {
        //     $conditions['seq_LTE'] = $this->paging['offsetSeq'];
        //     $conditions['seq_GTE'] = $this->paging['offsetSeq'] - $this->paging['limit'] + 1;
        // }

        return $conditions;
    }

    private function isHiddenUnpublishTasks()
    {
        $course = $this->getCourseService()->getCourse($this->courseId);

        return $course['isHideUnpublish'];
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CourseChapterDao
     */
    private function getChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }

    /**
     * @return TaskDao
     */
    private function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
    }
}
