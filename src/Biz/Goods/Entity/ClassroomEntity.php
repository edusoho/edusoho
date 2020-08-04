<?php

namespace Biz\Goods\Entity;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;

/**
 * Class ClassroomEntity
 */
class ClassroomEntity extends BaseGoodsEntity
{
    public function getTarget($goods)
    {
        $product = $this->getProduct($goods['productId']);
        $classroom = $this->getClassroomService()->getClassroom($product['targetId']);
        if (empty($classroom)) {
            throw ClassroomException::NOTFOUND_CLASSROOM();
        }

        return $classroom;
    }

    public function fetchTargets($goodses)
    {
        if (empty($goodses)) {
            return $goodses;
        }
        $productIds = ArrayToolkit::column($goodses, 'productId');
        $products = ArrayToolkit::index($this->getProductService()->findProductsByIds($productIds), 'id');
        $classroomIds = ArrayToolkit::column($products, 'targetId');
        $classrooms = ArrayToolkit::index($this->getClassroomService()->findClassroomsByIds($classroomIds), 'id');
        foreach ($goodses as &$goods) {
            $product = empty($products[$goods['productId']]) ? [] : $products[$goods['productId']];
            $classroom = empty($classrooms[$product['targetId']]) ? [] : $classrooms[$product['targetId']];
            $goods['product'] = $product;
            $goods['classroom'] = $classroom;
        }

        return $goodses;
    }

    public function canManageTarget($goods)
    {
        $classroom = $this->getTarget($goods);

        return $this->getClassroomService()->canManageClassroom($classroom['id']);
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
