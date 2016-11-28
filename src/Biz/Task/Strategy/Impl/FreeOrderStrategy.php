<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 28/11/2016
 * Time: 11:31
 */

namespace Biz\Task\Strategy\Impl;


use Biz\Task\Strategy\StrategyInterface;

class FreeOrderStrategy implements StrategyInterface
{
    private $biz = null;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    /**
     * 自由学习
     * @param $task
     * @return bool
     */
    public function canLearnTask($task)
    {
       return true;
    }

}