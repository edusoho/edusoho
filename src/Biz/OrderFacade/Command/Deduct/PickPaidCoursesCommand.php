<?php

namespace Biz\OrderFacade\Command\Deduct;

use AppBundle\Common\MathToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Goods\Service\GoodsService;
use Biz\OrderFacade\Command\Command;
use Biz\OrderFacade\Product\Product;
use Biz\System\Service\SettingService;

class PickPaidCoursesCommand extends Command
{
    /**
     * @param array $params
     *                      班级课程的使用关系依然由具体的班级和课程控制，商品之间没有嵌套关系
     */
    public function execute(Product $product, $params = [])
    {
        if ('classroom' !== $product->targetType) {
            return;
        }

        $specs = $this->getGoodsService()->getGoodsSpecs($product->targetId);

        $classroomSetting = $this->getSettingService()->get('classroom');

        if (empty($classroomSetting['discount_buy'])) {
            return;
        }

        $user = $this->getUser();

        list($paidCourses, $orderItems) = $this->getClassroomService()->findUserPaidCoursesInClassroom($user['id'], $specs['targetId']);

        $totalDeductAmount = 0;
        foreach ($orderItems as $item) {
            if ($item['pay_amount'] <= 0) {
                continue;
            }

            $deductAmount = MathToolkit::simple($item['pay_amount'], 0.01);
            $deduct = [
                'deduct_amount' => $deductAmount,
                'deduct_type' => 'paidCourse',
                'deduct_id' => $item['target_id'],
            ];
            $product->pickedDeducts[] = $deduct;
            $totalDeductAmount += $deductAmount;
        }

        $product->promotionPrice = $product->originPrice - $totalDeductAmount;
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return GoodsService
     */
    private function getGoodsService()
    {
        return $this->biz->service('Goods:GoodsService');
    }

    protected function getUser()
    {
        $biz = $this->biz;

        return $biz['user'];
    }
}
