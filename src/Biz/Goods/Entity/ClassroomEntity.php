<?php

namespace Biz\Goods\Entity;

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
