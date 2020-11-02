<?php

namespace Biz\OrderFacade\Command\Deduct;

use AppBundle\Common\MathToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\OrderFacade\Command\Command;
use Biz\OrderFacade\Product\Product;
use Biz\System\Service\SettingService;

/**
 * Class AvailablePaidCoursesCommand
 * 获取可用的抵扣手段，依赖于班级课程价格抵扣，业务围绕班级和课程，属于历史业务
 */
class AvailablePaidCoursesCommand extends Command
{
    public function execute(Product $product, $params = [])
    {
        if ('classroom' !== $product->targetType) {
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

            if (empty($paidCourses[$item['target_id']])) {
                continue;
            }

            $course = $paidCourses[$item['target_id']];
            $course['paidPrice'] = MathToolkit::simple($item['pay_amount'], 0.01);

            $product->availableDeducts['paidCourses'][] = $course;
            $totalDeductAmount += $course['paidPrice'];
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

    protected function getUser()
    {
        $biz = $this->biz;

        return $biz['user'];
    }
}
