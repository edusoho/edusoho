<?php

namespace Biz\Task\Visitor;

use AppBundle\Common\ArrayToolkit;
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

    }

    public function visitNormalStrategy(NormalStrategy $normalStrategy)
    {
        $conditions = $this->getConditions();

        $chapters = $this->getChapterDao()->search(
            array($conditions),
            array(),
            0,
            $this->paging['limit']
        );

        $tasks = $this->getTaskDao()->search(
            array($conditions),
            array(),
            0,
            $this->paging['limit']
        );

        $items = array_merge($chapters, $tasks);

        uasort($items, function($item1, $item2) {
            return $item1['seq'] > $item2['seq'];
        });

        return $items;
    }

    private function getConditions()
    {

        $limitSeq = $this->paging['offsetSeq'] + $this->paging['limit'];

        $conditions = array(
            'courseId' => $this->courseId,
        );


        if ($this->paging['direction'] == 'down') {
            $conditions['seq_GTE'] = $limitSeq;
        }

        if ($this->paging['direction'] == 'up') {
            $conditions['seq_LTE'] = $limitSeq;
        }

        return $conditions;
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

    private function startPaging()
    {
        switch ($this->paging['direction']) {
            case 'down':
                break;
            case 'up':
                break;
            default:
                break;
        }
    }
}
