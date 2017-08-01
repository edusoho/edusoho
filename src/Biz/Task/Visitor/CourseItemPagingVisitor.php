<?php

namespace Biz\Task\Visitor;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Service\CourseService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\Impl\DefaultStrategy;
use Biz\Task\Strategy\Impl\NormalStrategy;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class CourseItemPagingVisitor implements CourseStrategyVisitorInterface
{
    /**
     * @var Biz
     */
    private $biz;

    private $courseId;

    private $paging = array(
        'direction' => 'down',
        'limit' => 12,
        'offsetSeq' => 1
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

        $this->wrapTask($tasks);

        $items = array_merge($chapters, $tasks);

        foreach ($items as $key => &$item) {

            if ($item['type'] == 'chapter' || $item['type'] == 'unit') {
                $item['itemType'] = $item['type'];
            } else if ($item['type'] == 'lesson') {
                unset($items[$key]);
            } else {
                $item['itemType'] = 'task';
            }
        }

        uasort($items, function($item1, $item2) {
            return $item1['seq'] > $item2['seq'];
        });

        return $items;
    }

    private function wrapTask(&$tasks)
    {
        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities = $this->getActivityService()->findActivities($activityIds, true);
        $activities = ArrayToolkit::index($activities, 'id');

        foreach ($tasks as &$task) {
            $task['activity'] = $activities[$task['activityId']];
        }

        return $this->getTaskService()->wrapTaskResultToTasks($this->courseId, $tasks);
    }

    private function getConditions()
    {
        $conditions = array(
            'courseId' => $this->courseId,
        );

        if ($this->paging['direction'] == 'down') {
            $conditions['seq_GTE'] = $this->paging['offsetSeq'];
        }

        if ($this->paging['direction'] == 'up') {
            $conditions['seq_LTE'] = $this->paging['offsetSeq'];
        }

        return $conditions;
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
