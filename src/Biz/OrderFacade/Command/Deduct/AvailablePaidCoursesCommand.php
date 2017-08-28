<?php

namespace Biz\OrderFacade\Command\Deduct;

use Biz\OrderFacade\Command\Command;
use Biz\OrderFacade\Product\Product;

class AvailablePaidCoursesCommand extends Command
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
        $paidCourses = $this->getClassroomService()->findUserJoinedCoursesInClassroom($user['id'], $product->targetId);

        foreach ($paidCourses as $course) {
            if ($course['originPrice'] > 0) {
                $product->availableDeducts['paidCourses'][] = $course;
            }
        }
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
