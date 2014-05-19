<?php
namespace Topxia\Service\Course;

/**
 * 课程订单服务
 */
interface CourseOrderService
{

    /**
     * 处理成功支付的课程订单
     *
     * 在支付通知结果返回中，调用此接口
     */
    public function doSuccessPayOrder($order);

    /**
     * 取消课程的退款订单
     */
	public function cancelRefundOrder($id);
}