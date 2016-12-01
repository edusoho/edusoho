<?php

namespace Biz\Task\Strategy;


use Biz\Task\Strategy\Impl\DefaultStrategy;
use Biz\Task\Strategy\Impl\FreeModeStrategy;
use Biz\Task\Strategy\Impl\LockModeStrategy;
use Biz\Task\Strategy\Impl\PlanStrategy;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class StrategyContext
{
    private $strategy = null;

    private static $_instance = NULL;

    /**
     * 私有化默认构造方法，保证外界无法直接实例化
     */
    private function __construct()
    {
    }

    /**
     * 静态工厂方法，返还此类的唯一实例
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new StrategyContext();
        }

        return self::$_instance;
    }

    public function createStrategy($strategyType, $biz)
    {
        switch ($strategyType) {
            case 0:
                $this->strategy = new PlanStrategy($biz);
                break;
            case 1:
                $this->strategy = new DefaultStrategy($biz);
                break;
            default:
                throw new NotFoundException('teach method strategy does not exist');
        }
        return $this->strategy;
    }

    //任务的api策略
    public function createTask($fields)
    {
        return $this->strategy->createTask($fields);
    }

    public function updateTask($id, $fields)
    {
        return $this->strategy->updateTask($id, $fields);
    }

    public function canLearnTask($task)
    {
        return $this->strategy->canLearnTask($task);
    }

    public function getCourseItemsRenderPage()
    {
        return $this->strategy->getCourseItemsRenderPage();
    }

    public function getTasksRenderPage()
    {
        return $this->strategy->getTasksRenderPage();
    }

    //课程的api 策略
    public function findCourseItems($courseId)
    {
        return $this->strategy->findCourseItems($courseId);
    }

    public function sortCourseItems($courseId, array $itemIds)
    {
        return $this->strategy->sortCourseItems($courseId, $itemIds);
    }

}