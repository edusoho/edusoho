<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Service\Common\ServiceKernel;

class CourseOrderProcessor implements OrderProcessor
{
	protected $router = "course_show";

	public function getRouter() {
		return $this->router;
	}

	public function doPayReturn($success, $order) {
	    if (!$success) {
	        return $this->router;
	    }

	    $this->getCourseOrderService()->doSuccessPayOrder($order['id']);

	    return $this->router;
	}

	public function doPayNotify($success, $order) {
        if (!$success) {
            return ;
        }

        $this->getCourseOrderService()->doSuccessPayOrder($order['id']);

        return ;
    }

	protected function getCourseOrderService() {
		return ServiceKernel::instance()->createService("Course.CourseOrder");
	}
}