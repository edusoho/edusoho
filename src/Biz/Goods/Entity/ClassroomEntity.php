<?php

namespace Biz\Goods\Entity;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use VipPlugin\Biz\Vip\Service\LevelService;
use VipPlugin\Biz\Vip\Service\VipService;

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

    public function canBuySpecs($goods, $specs)
    {
        return $this->getClassroomService()->canJoinClassroom($specs['targetId']);
    }

    public function isSpecsStudent($goods, $specs, $userId)
    {
        return $this->getClassroomService()->isClassroomStudent($specs['targetId'], $userId);
    }

    public function getVipInfo($goods, $specs, $userId)
    {
        $vipUser = $this->getVipService()->getMemberByUserId($userId);
        $classroom = $this->getClassroomService()->getClassroom($specs['targetId']);
        if ($classroom['vipLevelId']) {
            return [$this->getVipLevelService()->getLevel($classroom['vipLevelId']), $vipUser];
        }

        return [null, $vipUser];
    }

    /**
     * @param $goods
     * @param $specs
     * @param $userId
     *
     * @return bool
     *              对班级，老师、助教都是老师
     */
    public function isSpecsTeacher($goods, $specs, $userId)
    {
        return $this->getClassroomService()->isClassroomTeacher($specs['targetId'], $userId)
            && $this->getClassroomService()->isClassroomHeadTeacher($specs['targetId'], $userId)
            && $this->getClassroomService()->isClassroomAssistant($specs['targetId'], $userId);
    }

    public function isSpecsMember($goods, $specs, $userId)
    {
        return $this->isSpecsTeacher($goods, $specs, $userId)
            && $this->isSpecsStudent($goods, $specs, $userId);
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    /**
     * @return LevelService
     */
    protected function getVipLevelService()
    {
        return $this->biz->service('VipPlugin:Vip:LevelService');
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->biz->service('VipPlugin:Vip:VipService');
    }
}
