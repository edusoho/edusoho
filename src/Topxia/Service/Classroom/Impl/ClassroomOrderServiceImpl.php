<?php

namespace Topxia\Service\Classroom\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Classroom\ClassroomOrderService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceKernel;


class ClassroomOrderServiceImpl extends BaseService implements ClassroomOrderService 
{
	public function createOrder($info)
    {
        $connection = ServiceKernel::instance()->getConnection();
        try {
            $connection->beginTransaction();
            
            $user = $this->getCurrentUser();
            if (!$user->isLogin()) {
                throw $this->createServiceException('用户未登录，不能创建订单');
            }

            if (!ArrayToolkit::requireds($info, array('targetId', 'payment'))) {
                throw $this->createServiceException('订单数据缺失，创建课程订单失败。');
            }

            // 获得锁
            $user = $this->getUserService()->getUser($user['id'], true);

            if ($this->getClassroomService()->isClassroomStudent($info['targetId'], $user['id'])) {
                throw $this->createServiceException('已经是班级学员，创建订单失败。');
            }

            $classroom = $this->getClassroomService()->getClassroom($info['targetId']);
            if (empty($classroom)) {
                throw $this->createServiceException('班级不存在，创建课程订单失败。');
            }

            $this->cancelOldOrders($classroom, $user);

            $order = array();

            $order['userId'] = $user['id'];
            $order['title'] = "购买班级《{$classroom['title']}》";
            $order['targetType'] = 'classroom';
            $order['targetId'] = $classroom['id'];
            $order['payment'] = $info['payment'];
            $order['amount'] = empty($info['amount'])? 0 : $info['amount'];
            $order['priceType'] = $info['priceType'];
            $order['totalPrice'] = $info["totalPrice"];
            $order['coinRate'] = $info['coinRate'];
            $order['coinAmount'] = $info['coinAmount'];
            $order['snPrefix'] = 'CR';

            if (!empty($info['note'])) {
                $order['data'] = array('note' => $info['note']);
            }

            $order = $this->getOrderService()->createOrder($order);
            if (empty($order)) {
                throw $this->createServiceException('创建订单失败！');
            }

            // 免费课程，就直接将订单置为已购买
            if (intval($order['amount']*100) == 0 && intval($order['coinAmount']*100) == 0 && empty($order['coupon'])) {
                list($success, $order) = $this->getOrderService()->payOrder(array(
                    'sn' => $order['sn'],
                    'status' => 'success', 
                    'amount' => $order['amount'], 
                    'paidTime' => time()
                ));

                $info = array(
                    'orderId' => $order['id'],
                    'remark'  => empty($order['data']['note']) ? '' : $order['data']['note'],
                );
                $this->getClassroomService()->becomeStudent($order['targetId'], $order['userId'], $info);
            }

            $connection->commit();

            return $order;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

    }

    public function doSuccessPayOrder($id)
    {
        $order = $this->getOrderService()->getOrder($id);
        if (empty($order) or $order['targetType'] != 'classroom') {
            throw $this->createServiceException('非课程订单，加入课程失败。');
        }

        $info = array(
            'orderId' => $order['id'],
            'remark'  => empty($order['data']['note']) ? '' : $order['data']['note'],
        );

        if (!$this->getClassroomService()->isClassroomStudent($order['targetId'], $order['userId'])) {
            $this->getClassroomService()->becomeStudent($order['targetId'], $order['userId'], $info);
        }

        return ;
    }

    private function cancelOldOrders($classroom, $user)
    {
        $conditions = array(
            'userId' => $user['id'],
            'status' => 'created',
            'targetType' => 'classroom',
            'targetId' => $classroom['id'],
        );
        $count = $this->getOrderService()->searchOrderCount($conditions);

        if ($count == 0) {
            return ;
        }

        $oldOrders = $this->getOrderService()->searchOrders($conditions, array('createdTime', 'DESC'), 0, $count);

        foreach ($oldOrders as $order) {
            $this->getOrderService()->cancelOrder($order['id'], '系统自动取消');
        }

    }

    protected function getOrderService()
    {
        return $this->createService('Order.OrderService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCashService()
    {
        return $this->createService('Cash.CashService');
    }

    protected function getCashAccountService()
    {
        return $this->createService('Cash.CashAccountService');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

}
