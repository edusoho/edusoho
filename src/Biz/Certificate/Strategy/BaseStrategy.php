<?php

namespace Biz\Certificate\Strategy;

use Biz\Taxonomy\Service\CategoryService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Context\Biz;
use AppBundle\Common\ArrayToolkit;

abstract class BaseStrategy
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    abstract public function getTargetModal($targets);

    abstract public function count($conditions);

    abstract public function search($conditions, $orderBys, $start, $limit);
}