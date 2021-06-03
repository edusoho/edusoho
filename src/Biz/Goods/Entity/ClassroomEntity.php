<?php

namespace Biz\Goods\Entity;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use VipPlugin\Biz\Marketing\Service\VipRightService;
use VipPlugin\Biz\Marketing\VipRightSupplier\ClassroomVipRightSupplier;
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

    public function hitTarget($goods)
    {
        $product = $this->getProduct($goods['productId']);
        $this->getClassroomService()->hitClassroom($product['targetId']);
        $classroom = $this->getClassroomService()->getClassroom($product['targetId']);

        return $classroom['hitNum'];
    }

    public function getSpecsByTargetId($targetId)
    {
        $product = $this->getProductService()->getProductByTargetIdAndType($targetId, 'classroom');

        return $this->getGoodsService()->getGoodsSpecsByProductIdAndTargetId($product['id'], $targetId);
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

    public function fetchSpecs($classrooms)
    {
        if (empty($classrooms)) {
            return $classrooms;
        }

        foreach ($classrooms as &$classroom) {
            $classroom['spec'] = $this->getSpecsByTargetId($classroom['id']);
            $classroom['goodsId'] = empty($classroom['spec']) ? 0 : $classroom['spec']['goodsId'];
            $classroom['specsId'] = empty($classroom['spec']) ? 0 : $classroom['spec']['id'];
            $goods = $this->getGoodsService()->getGoods($classroom['goodsId']);
            $classroom['hitNum'] = empty($goods['hitNum']) ? 0 : $goods['hitNum'];
        }

        return $classrooms;
    }

    public function canManageTarget($goods)
    {
        $classroom = $this->getTarget($goods);

        return $this->getClassroomService()->canManageClassroom($classroom['id']);
    }

    public function buySpecsAccess($goods, $specs)
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
        if ($vipUser) {
            $vipUser['level'] = $this->getVipLevelService()->getLevel($vipUser['levelId']);
        }

        $vipRight = $this->getVipRightService()->getVipRightsBySupplierCodeAndUniqueCode(ClassroomVipRightSupplier::CODE, $specs['targetId']);
        if ($vipRight) {
            return [$this->getVipLevelService()->getLevel($vipRight['vipLevelId']), $vipUser];
        }

        return [null, $vipUser];
    }

    public function canVipFreeJoin($goods, $specs, $userId)
    {
        if (!$this->isPluginInstalled('vip')) {
            return false;
        }
        $classroom = $this->getClassroomService()->getClassroom($specs['targetId']);

        return 'ok' === $this->getVipService()->checkUserVipRight($userId, ClassroomVipRightSupplier::CODE, $classroom['id']);
    }

    public function getSpecsTeacherIds($goods, $specs)
    {
        return $this->getClassroomService()->findTeachers($specs['targetId']);
    }

    public function hasCertificate($goods, $specs)
    {
        return $this->getClassroomService()->hasCertificate($specs['targetId']);
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
            || $this->getClassroomService()->isClassroomHeadTeacher($specs['targetId'], $userId)
            || $this->getClassroomService()->isClassroomAssistant($specs['targetId'], $userId);
    }

    public function isSpecsMember($goods, $specs, $userId)
    {
        return $this->isSpecsStudent($goods, $specs, $userId) || $this->isSpecsTeacher($goods, $specs, $userId);
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

    /**
     * @return VipRightService
     */
    protected function getVipRightService()
    {
        return $this->biz->service('VipPlugin:Marketing:VipRightService');
    }
}
