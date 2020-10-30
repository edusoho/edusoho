<?php

namespace Biz\Goods;

use AppBundle\Common\Exception\UnexpectedValueException;
use Biz\Goods\Entity\BaseGoodsEntity;
use Biz\Goods\Entity\ClassroomEntity;
use Biz\Goods\Entity\CourseEntity;
use Pimple\Container;

class GoodsEntityFactory
{
    protected $biz;

    protected $map = [
        'course' => CourseEntity::class,
        'classroom' => ClassroomEntity::class,
    ];

    /**
     * GoodsEntityFactory constructor.
     */
    public function __construct(Container $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @param $type
     *
     * @return BaseGoodsEntity
     */
    public function create($type)
    {
        if (empty($this->map[$type])) {
            throw new UnexpectedValueException('goods entity could not be found');
        }

        return new $this->map[$type]($this->biz);
    }
}
