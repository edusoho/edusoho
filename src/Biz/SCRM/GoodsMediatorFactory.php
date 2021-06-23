<?php

namespace Biz\SCRM;

use Biz\Common\CommonException;
use Biz\SCRM\GoodsMediator\AbstractMediator;
use Biz\SCRM\GoodsMediator\Classroom;
use Biz\SCRM\GoodsMediator\Course;
use Codeages\Biz\Framework\Context\Biz;

/**
 * Class GoodsMediatorFactory
 * @package Biz\SCRM
 * 原则：不使用Extension开放自由扩展，而是用固定的工厂构建，因为商品类型不允许被随意扩展和改动，也不允许覆写
 */
class GoodsMediatorFactory
{
    /**
     * @var Biz
     */
    protected $biz;

    /**
     * GoodsMediatorFactory constructor.
     * @param Biz $biz
     */
    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @param $type
     * @return AbstractMediator
     */
    public function create($type)
    {
        $types = self::types();
        if (empty($types[$type])) {
            throw CommonException::ERROR_PARAMETER();
        }
        return new $types[$type]($this->biz);
    }

    private static function types()
    {
        return [
            'course' => Course::class,
            'classroom' => Classroom::class,
        ];
    }
}