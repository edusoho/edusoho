<?php
namespace Topxia\Service\Course;

/**
 * 课程订单服务
 */
interface CourseOrderService
{

    /**
     * 根据订单信息，创建订单
     */
    public function createOrder($info);

    /**
     * 处理成功支付的课程订单
     *
     * 在支付通知结果返回中，调用此接口
     */
    public function doSuccessPayOrder($id);

    /**
     * 申请退款
     * 
     * @param  integer $id     订单号
     * @param  float $amount 申请的期望退款金额
     * @param  string $reason 退款理由
     * @param  Container $container 此参数要去除
     * @return array         退款记录
     */
    public function applyRefundOrder($id, $amount, $reason, $container);

    /**
     * 取消课程的退款订单
     */
	public function cancelRefundOrder($id);

    public function updateOrder($id, $orderFileds);
}