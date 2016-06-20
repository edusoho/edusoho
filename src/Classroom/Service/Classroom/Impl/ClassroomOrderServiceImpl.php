<?php
namespace Classroom\Service\Classroom\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceKernel;
use Classroom\Service\Classroom\ClassroomOrderService;

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

            $info['vipStatus'] = !empty($info['vipStatus']) ? $info['vipStatus'] : null;

            if ($classroom['buyable'] == 0 && $info['vipStatus'] != 'ok') {
                throw $this->createServiceException('该班级是封闭班级，学员不能自行加入！');
            }

            $order               = array();
            $classroomSetting    = $this->getSettingService()->get('classroom');
            $classroomName       = isset($classroomSetting['name']) ? $classroomSetting['name'] : '班级';
            $order['userId']     = $user['id'];
            $order['title']      = "购买".$classroomName."《{$classroom['title']}》";
            $order['targetType'] = 'classroom';
            $order['targetId']   = $classroom['id'];
            $order['payment']    = $info['payment'];
            $order['amount']     = empty($info['amount']) ? 0 : $info['amount'];
            $order['priceType']  = $info['priceType'];
            $order['totalPrice'] = $info["totalPrice"];
            $order['coinRate']   = $info['coinRate'];
            $order['coinAmount'] = $info['coinAmount'];
            $order['snPrefix']   = 'CR';

            if (!empty($info['note'])) {
                $order['data'] = array('note' => $info['note']);
            }

            if (!empty($info['coupon'])) {
                $order['coupon']         = $info['coupon'];
                $order['couponDiscount'] = $info['couponDiscount'];
            }

            $order = $this->getOrderService()->createOrder($order);

            if (empty($order)) {
                throw $this->createServiceException('创建订单失败！');
            }

            // 免费班级或VIP会员，就直接将订单置为已购买

            if ((intval($order['amount'] * 100) == 0 && intval($order['coinAmount'] * 100) == 0
                && empty($order['coupon'])) || !empty($info["becomeUseMember"])) {
                list($success, $order) = $this->getOrderService()->payOrder(array(
                    'sn'       => $order['sn'],
                    'status'   => 'success',
                    'amount'   => $order['amount'],
                    'paidTime' => time()
                ));

                $info = array(
                    'orderId' => $order['id'],
                    'remark'  => empty($order['data']['note']) ? '' : $order['data']['note']
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

        if (empty($order) || $order['targetType'] != 'classroom') {
            throw $this->createServiceException('非课程订单，加入课程失败。');
        }

        $info = array(
            'orderId' => $order['id'],
            'remark'  => empty($order['data']['note']) ? '' : $order['data']['note']
        );

        $classroomSetting         = $this->getSettingService()->get('classroom');
        $classroomSetting['name'] = empty($classroomSetting['name']) ? '班级' : $classroomSetting['name'];

        if (!$this->getClassroomService()->isClassroomStudent($order['targetId'], $order['userId'])) {
            $this->getClassroomService()->becomeStudent($order['targetId'], $order['userId'], $info);
        } else {
            $this->getOrderService()->createOrderLog($order['id'], "pay_success", "当前用户已经是{$classroomSetting['name']}学员，支付成功。", $order);
            $this->getLogService()->warning("classroom_order", "pay_success", "当前用户已经是{$classroomSetting['name']}学员，支付成功。", $order);
        }

        return;
    }

    public function applyRefundOrder($id, $amount, $reason, $container)
    {
        $user  = $this->getCurrentUser();
        $order = $this->getOrderService()->getOrder($id);

        if (empty($order)) {
            throw $this->createServiceException('订单不存在，不能申请退款。');
        }

        $refund = $this->getOrderService()->applyRefundOrder($id, $amount, $reason);

        if ($refund['status'] == 'created') {
            $this->getClassroomService()->lockStudent($order['targetId'], $order['userId']);

            $setting      = $this->getSettingService()->get('refund');
            $message      = (empty($setting) || empty($setting['applyNotification'])) ? '' : $setting['applyNotification'];
            $classroom    = $this->getClassroomService()->getClassroom($order["targetId"]);
            $classroomUrl = $container->get('router')->generate('classroom_show', array('id' => $classroom['id']));

            if ($message) {
                $variables = array(
                    'item' => "<a href='{$classroomUrl}'>{$classroom['title']}</a>"
                );
                $message = StringToolkit::template($message, $variables);
                $this->getNotificationService()->notify($refund['userId'], 'default', $message);
            }

            $classroomSetting         = $this->getSettingService()->get('classroom');
            $classroomSetting['name'] = empty($classroomSetting['name']) ? '班级' : $classroomSetting['name'];

            $adminmessage = '用户'."{$user['nickname']}".'申请退款'."<a href='{$classroomUrl}'>{$classroom['title']}</a>"."{$classroomSetting['name']}，请审核。";
            $adminCount   = $this->getUserService()->searchUserCount(array('roles' => 'ADMIN'));
            $admins       = $this->getUserService()->searchUsers(array('roles' => 'ADMIN'), array('id', 'DESC'), 0, $adminCount);

            foreach ($admins as $key => $admin) {
                $this->getNotificationService()->notify($admin['id'], 'default', $adminmessage);
            }
        } elseif ($refund['status'] == 'success') {
            $this->getClassroomService()->exitClassroom($order['targetId'], $order['userId']);
        }

        return $refund;
    }

    public function getOrder($id)
    {
        return $this->getOrderService()->getOrder($id);
    }

    public function cancelRefundOrder($id)
    {
        $order = $this->getOrderService()->getOrder($id);

        if (empty($order) || $order['targetType'] != 'classroom') {
            throw $this->createServiceException('订单不存在，取消退款申请失败。');
        }

        $this->getOrderService()->cancelRefundOrder($id);

        if ($this->getClassroomService()->isClassroomStudent($order['targetId'], $order['userId'])) {
            $this->getClassroomService()->unlockStudent($order['targetId'], $order['userId']);
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

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getNotificationService()
    {
        return $this->createService('User.NotificationService');
    }
}
