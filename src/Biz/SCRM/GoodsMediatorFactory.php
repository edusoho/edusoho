<?php

namespace Biz\SCRM;

use Biz\Common\CommonException;
use Biz\SCRM\GoodsMediator\AbstractMediator;
use Biz\SCRM\GoodsMediator\Classroom;
use Biz\SCRM\GoodsMediator\Course;
use Codeages\Biz\Framework\Context\Biz;

/**
 * Class GoodsMediatorFactory
 */
class GoodsMediatorFactory
{
    /**
     * @var Biz
     */
    protected $biz;

    /**
     * GoodsMediatorFactory constructor.
     */
    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @param $type
     *
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
