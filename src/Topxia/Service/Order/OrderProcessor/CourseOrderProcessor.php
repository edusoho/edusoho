<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Service\Common\ServiceKernel;

class CourseOrderProcessor implements OrderProcessor
{
	protected $router = "course_show";

	public function getRouter() {
		return $this->router;
	}

	public function doPaySuccess($success, $order) {
        if (!$success) {
            return ;
        }

        $this->getCourseOrderService()->doSuccessPayOrder($order['id']);

        return ;
    }

	protected function getCourseOrderService() {
		return ServiceKernel::instance()->createService("Course.CourseOrderService");
	}
}