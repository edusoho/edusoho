<?php

namespace Biz\OrderFacade\Command\Deduct;

use Biz\OrderFacade\Command\Command;
use Biz\OrderFacade\Product\Product;
use AppBundle\Common\MathToolkit;

class PickPaidCoursesCommand extends Command
{
    public function execute(Product $product, $params = array())
    {
        if ($product->targetType != 'classroom') {
            return;
        }

        $classroomSetting = $this->getSettingService()->get('classroom');

        if (empty($classroomSetting['discount_buy'])) {
            return;
        }

        $user = $this->getUser();

        list($paidCourses, $orderItems) = $this->getClassroomService()->findUserPaidCoursesInClassroom($user['id'], $product->targetId);

        $totalDeductAmount = 0;
        foreach ($orderItems as $item) {
            if ($item['pay_amount'] <= 0) {
                continue;
            }

            $deductAmount = MathToolkit::simple($item['pay_amount'], 0.01);
            $deduct = array(
                'deduct_amount' => $deductAmount,
                'deduct_type' => 'paidCourse',
                'deduct_id' => $item['target_id'],
            );
            $product->pickedDeducts[] = $deduct;
            $totalDeductAmount += $deductAmount;
        }

        $product->promotionPrice = $product->originPrice - $totalDeductAmount;
    }

    private function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    protected function getUser()
    {
        $biz = $this->biz;

        return $biz['user'];
    }
}
