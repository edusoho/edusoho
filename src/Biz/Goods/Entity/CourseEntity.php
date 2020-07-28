<?php

namespace Biz\Goods\Entity;

use Biz\Course\CourseSetException;
use Biz\Course\Service\CourseSetService;

/**
 * Class CourseEntity
 */
class CourseEntity extends BaseGoodsEntity
{
    public function getTarget($goods)
    {
        $product = $this->getProduct($goods['productId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($product['targetId']);
        if (empty($courseSet)) {
            throw CourseSetException::NOTFOUND_COURSESET();
        }

        return $courseSet;
    }

    public function canManageTarget($goods)
    {
        $courseSet = $this->getTarget($goods);

        return $this->getCourseSetService()->hasCourseSetManageRole($courseSet['id']);
    }

    /**
     * @return CourseSetService
     */
    public function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }
}
