<?php

namespace Biz\Goods\Entity;

use AppBundle\Common\ArrayToolkit;
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

    public function fetchTargets($goodses)
    {
        if (empty($goodses)) {
            return $goodses;
        }
        $productIds = ArrayToolkit::column($goodses, 'productId');
        $products = ArrayToolkit::index($this->getProductService()->findProductsByIds($productIds), 'id');
        $courseSetIds = ArrayToolkit::column($products, 'targetId');
        $courseSets = ArrayToolkit::index($this->getCourseSetService()->findCourseSetsByIds($courseSetIds), 'id');
        foreach ($goodses as &$goods) {
            $product = empty($products[$goods['productId']]) ? [] : $products[$goods['productId']];
            $courseSet = empty($courseSets[$product['targetId']]) ? [] : $courseSets[$product['targetId']];
            $goods['product'] = $product;
            $goods['courseSet'] = $courseSet;
        }

        return $goodses;
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
