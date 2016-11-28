<?php

namespace Biz\Task\Strategy;


use Biz\Task\Strategy\Impl\ByOrderStrategy;
use Biz\Task\Strategy\Impl\FreeOrderStrategy;
use Biz\Task\Strategy\Impl\TaskByOrderStrategy;
use Biz\Task\Strategy\Impl\TaskFreeOrderStrategy;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class StrategyContext
{
    private $strategy = null;

    public function __construct($strategy_ind_id, $biz)
    {
        switch ($strategy_ind_id) {
            case 'byOrder':
                $this->strategy = new ByOrderStrategy($biz);
                break;
            case 'freeOrder':
                $this->strategy = new FreeOrderStrategy($biz);
                break;
            default:
                throw new NotFoundException('teach method does not exist');

        }
    }

    public function canLearnTask($task)
    {
        return $this->strategy->canLearnTask($task);
    }
}