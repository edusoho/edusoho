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
		$order['title'] = "用户:"."{$user['nickname']} 购买了 课程:{$course['title']}";
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

	public function findOrderLogs($orderId)
	{
		$order = $this->getOrder($orderId);
		if(empty($order)){
			throw $this->createServiceException("订单不存在，获取订单日志失败！");
		}
		return $this->getOrderLogDao()->findLogsByOrderId($orderId);
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

	public function searchOrders($conditions, $sort = 'latest', $start, $limit)
	{
		$orderBy = array();
		if ($sort == 'latest') {
			$orderBy =  array('createdTime', 'DESC');
		} else {
			$orderBy = array('createdTime', 'DESC');
		}

		$conditions = $this->_prepareSearchConditions($conditions);
		$orders = $this->getOrderDao()->searchOrders($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($orders, 'id');
	}

	public function searchOrderCount($conditions)
	{
		$conditions = $this->_prepareSearchConditions($conditions);
		return $this->getOrderDao()->searchOrderCount($conditions);
	}

	private function _prepareSearchConditions($conditions)
	{
		$conditions = array_filter($conditions);
		
		if (isset($conditions['date'])) {
			$dates = array(
				'yesterday'=>array(
					strtotime('yesterday'),
					strtotime('today'),
				),
				'today'=>array(
					strtotime('today'),
					strtotime('tomorrow'),
				),
				'this_week' => array(
					strtotime('Monday this week'),
					strtotime('Monday next week'),
				),
				'last_week' => array(
					strtotime('Monday last week'),
					strtotime('Monday this week'),
				),
				'next_week' => array(
					strtotime('Monday next week'),
					strtotime('Monday next week', strtotime('Monday next week')),
				),
				'this_month' => array(
					strtotime('first day of this month midnight'), 
					strtotime('first day of next month midnight'),
				),
				'last_month' => array(
					strtotime('first day of last month midnight'),
					strtotime('first day of this month midnight'),
				),
				'next_month' => array(
					strtotime('first day of next month midnight'),
					strtotime('first day of next month midnight', strtotime('first day of next month midnight')),
				),
			);

			if (array_key_exists($conditions['date'], $dates)) {
				$conditions['paidStartTime'] = $dates[$conditions['date']][0];
				$conditions['paidEndTime'] = $dates[$conditions['date']][1];
				unset($conditions['date']);
			}
		}
		
        if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
        }
        unset($conditions['keywordType']);
        unset($conditions['keyword']);

        if (isset($conditions['buyer'])) {
        	$user = $this->getUserService()->getUserByNickname($conditions['buyer']);
        	$conditions['userId'] = $user ? $user['id'] : -1;
        }

        return $conditions;
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

	private function getUserService()
	{
		return $this->createService('User.UserService');
	}

}