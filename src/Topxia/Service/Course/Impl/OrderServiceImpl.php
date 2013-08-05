<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\OrderService;
use Topxia\Common\ArrayToolkit;

class OrderServiceImpl extends BaseService implements OrderService
{

	public function getOrder($id)
	{
		return $this->getOrderDao()->getOrder($id);
	}

	public function createOrder($order)
	{
		$user = $this->getCurrentUser();
		$course = $this->getCourseService()->getCourse($order['courseId']);

		$order['sn'] = $this->generateOrderSn($order);
		$order['title'] = "课程《{$course['title']}》";
		$order['price'] = $course['price'];
		if (intval($order['price']*100) == 0) {
			$order['status'] = 'paid';
			$order['payment'] = 'none';
			$order['paidTime'] = time();
		}
		$order['userId'] = $user['id'];
		$order['createdTime'] = time();

		return $this->getOrderDao()->addOrder($order);
	}

	public function payOrder($payData)
	{
		$order = $this->getOrderDao()->getOrderBySn($payData['sn']);
		if (empty($order)) {
			throw $this->createNotFoundException("订单({$response->getSn()})已被删除！");
		}

		if ($payData['status'] == 'success') {
			// 避免浮点数比较大小可能带来的问题，转成整数再比较。
			if (intval($payData['amount']*100) !== intval($order['price']*100)) {
				$message = sprintf('订单(%s)的金额(%s)与实际支付的金额(%s)不一致。', array($order['sn'], $order['price'], $payData['amount']));
				$this->_createLog($order['id'], 'pay_error', $message, $payData);
				throw \RuntimeException($message);
			}

			if ($this->canOrderPay($order)) {
				$this->getOrderDao()->updateOrder($order['id'], array(
					'status' => 'paid',
					'paidTime' => $payData['paidTime'],
				));
				$this->_createLog($order['id'], 'pay_success', '付款成功', $payData);
				$this->getCourseService()->joinCourse($order['userId'], $order['courseId']);
			} else {
				$this->_createLog($order['id'], 'pay_ignore', '付款被忽略', $payData);
			}
		} else {
			$this->_createLog($order['id'], 'pay_unknown', '', $payData);
		}

		return $this->getOrder($order['id']);
	}

	private function _createLog($orderId, $type, $message = '', array $data = array())
	{
		$user = $this->getCurrentUser();

		$log = array(
			'orderId' => $orderId,
			'type' => $type,
			'message' => $message,
			'data' => json_encode($data),
			'userId' => $this->getCurrentUser()->id,
			'ip' => $this->getCurrentUser()->currentIp,
		);

		return $this->getOrderLogDao()->addLog($log);
	}

	public function canOrderPay($order)
	{
		if (empty($order['status'])) {
			throw new \InvalidArgumentException();
		}
		return in_array($order['status'], array('created'));
	}

	public function cancelOrder($id, $message = '')
	{

	}

	public function searchOrders($conditions, $order, $start, $limit)
	{

	}

	private function generateOrderSn($order)
	{
		return  'C' . date('YmdHis', time()) . mt_rand(10000,99999);
	}

	private function getOrderDao()
	{
		return $this->createDao('Course.OrderDao');
	}

	private function getOrderLogDao()
	{
		return $this->createDao('Course.OrderLogDao');
	}

	private function getCourseService()
	{
		return $this->createService('Course.CourseService');
	}

}