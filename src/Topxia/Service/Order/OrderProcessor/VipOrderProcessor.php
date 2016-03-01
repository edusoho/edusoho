<?php
namespace Topxia\Service\Order\OrderProcessor;

use Exception;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\NumberToolkit;
use Topxia\Service\Common\ServiceKernel;

class VipOrderProcessor extends BaseProcessor implements OrderProcessor
{
    protected $router = "vip";

    public function preCheck($targetId, $userId)
    {
        $member      = $this->getVipService()->getMemberByUserId($userId);
        $targetLevel = $this->getLevelService()->getLevel($targetId);
        $memberLevel = $this->getLevelService()->getLevel($member['levelId']);

        if ($memberLevel['seq'] > $targetLevel['seq']) {
            return array('error' => '已经是该等级会员了!');
        }

        return array();
    }

    public function getOrderInfo($targetId, $fields)
    {
        $user = $this->getUserService()->getCurrentUser();

        $level = $this->getLevelService()->getLevel($fields['targetId']);

        if (empty($level)) {
            throw new Exception("找不到会员等级!");
        }

        $member = $this->getVipService()->getMemberByUserId($user->id);

        if ($member) {
            if (array_key_exists("buyType", $fields) && $fields['buyType'] == "upgrade") {
                $buyType = "upgrade";
            } else {
                $buyType = "renew";
            }
        } else {
            $buyType = "new";
        }

        $levelPrice = array(
            'month' => $level['monthPrice'],
            'year'  => $level['yearPrice']
        );

        list($coinEnable, $priceType, $cashRate) = $this->getCoinSetting();

        if ($buyType == "upgrade") {
            $totalPrice = $this->getVipService()->calUpgradeMemberAmount($user->id, $level['id']);
        } else {
            if (!ArrayToolkit::requireds($fields, array("unit", "duration"))) {
                throw new Exception("参数不正确!");
            }

            $unitType  = $fields['unit'];
            $duration  = $fields['duration'];
            $unitPrice = $levelPrice[$unitType];

            if ($priceType == "Coin") {
                $unitPrice = NumberToolkit::roundUp($unitPrice * $cashRate);
            }

            $totalPrice = $unitPrice * $duration;
        }

        if (!$coinEnable) {
            return array(
                'totalPrice' => $totalPrice,
                'targetId'   => $targetId,
                'targetType' => "vip",

                'level'      => empty($level) ? null : $level,
                'unitType'   => empty($unitType) ? null : $unitType,
                'duration'   => empty($duration) ? null : $duration,
                'buyType'    => empty($buyType) ? null : $buyType
            );
        }

        list($totalPrice, $coinPayAmount, $account, $hasPayPassword) = $this->calculateCoinAmount($totalPrice, $priceType, $cashRate);

        if ($priceType == "Coin") {
            $maxCoin = $coinPayAmount;
        } else {
            $maxCoin = NumberToolkit::roundUp($level['maxRate'] * $totalPrice / 100 * $cashRate);
        }

        return array(
            'level'          => empty($level) ? null : $level,
            'unitType'       => empty($unitType) ? null : $unitType,
            'duration'       => empty($duration) ? null : $duration,
            'buyType'        => empty($buyType) ? null : $buyType,

            'totalPrice'     => $totalPrice,
            'targetId'       => $targetId,
            'targetType'     => "vip",
            'cashRate'       => $cashRate,
            'priceType'      => $priceType,
            'account'        => $account,
            'hasPayPassword' => $hasPayPassword,
            'coinPayAmount'  => $coinPayAmount,
            'maxCoin'        => $maxCoin
        );
    }

    public function shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $orderData)
    {
        if (!ArrayToolkit::requireds($orderData, array('buyType', 'targetId', 'unitType', 'duration'))) {
            throw new Exception('订单数据缺失，创建会员订单失败。');
        }

        if (!in_array($orderData['buyType'], array('new', 'renew', 'upgrade'))) {
            throw new Exception('购买类型不正确，创建会员订单失败。');
        }

        if (!(array_key_exists("buyType", $orderData) && $orderData["buyType"] == "upgrade")) {
            $orderData['duration'] = intval($orderData['duration']);

            if (empty($orderData['duration'])) {
                throw new Exception('会员开通时长不正确，创建会员订单失败。');
            }

            if (!in_array($orderData['unitType'], array('month', 'year'))) {
                throw new Exception('付费方式不正确，创建会员订单失败。');
            }
        }

        $level = $this->getLevelService()->getLevel($orderData['targetId']);

        if (empty($level)) {
            throw new Exception('会员等级不存在，创建会员订单失败。');
        }

        if (empty($level['enabled'])) {
            throw new Exception('会员等级已关闭，创建会员订单失败。');
        }

        $currentUser = $this->getLevelService()->getCurrentUser();

        if (array_key_exists("buyType", $orderData) && $orderData["buyType"] == "upgrade") {
            $totalPrice = $this->getVipService()->calUpgradeMemberAmount($currentUser->id, $level['id']);
        } else {
            $unitPrice = $level[$orderData['unitType'].'Price'];

            if ($priceType == "Coin") {
                $unitPrice = NumberToolkit::roundUp($unitPrice * $cashRate);
            }

            $totalPrice = $unitPrice * $orderData['duration'];
        }

        $amount = $totalPrice;
        //优惠码优惠价格

        if ($orderData["couponCode"] && trim($orderData["couponCode"]) != "") {
            $couponResult = $this->afterCouponPay(
                $orderData["couponCode"],
                'vip',
                $targetId,
                $totalPrice,
                $priceType,
                $cashRate
            );

            if (isset($couponResult["useable"]) && $couponResult["useable"] == "yes" && isset($couponResult["afterAmount"])) {
                $amount = $couponResult["afterAmount"];
            }
        }

        //虚拟币优惠价格

        if (array_key_exists("coinPayAmount", $orderData)) {
            $amount = $this->afterCoinPay(
                $coinEnabled,
                $priceType,
                $cashRate,
                $amount,
                $orderData['coinPayAmount'],
                $orderData["payPassword"]
            );
        }

        if ($priceType == "Coin") {
            $amount = $amount / $cashRate;
        }

        if ($amount <= 0) {
            $amount = 0;
        }

        $totalPrice = NumberToolkit::roundUp($totalPrice);
        $amount     = NumberToolkit::roundUp($amount);

        return array(
            $amount,
            $totalPrice,
            empty($couponResult) ? null : $couponResult
        );
    }

    public function createOrder($orderInfo, $fields)
    {
        $level = $this->getLevelService()->getLevel($orderInfo['targetId']);

        $unitNames = array('month' => '个月', 'year' => '年');

        if (array_key_exists("buyType", $fields) && $fields["buyType"] == "upgrade") {
            $orderInfo['title']    = "升级会员到 {$level['name']}";
            $orderInfo['snPrefix'] = 'M';
        } else {
            $orderInfo['title'] = ($fields['buyType'] == 'renew' ? '续费' : '购买');
            $orderInfo['title'] .= "{$level['name']} x {$fields['duration']}";
            $orderInfo['title'] .= "{$unitNames[$fields['unitType']]}";
            $orderInfo['title'] .= "{$level['name']}会员";

            $orderInfo['snPrefix'] = 'V';
        }

        $orderInfo['targetType'] = 'vip';
        $orderInfo['data']       = $fields;

        return $this->getOrderService()->createOrder($orderInfo);
    }

    public function doPaySuccess($success, $order)
    {
        if (!$success) {
            return;
        }

        if ($order['data']['buyType'] == 'new') {
            $vip = $this->getVipService()->becomeMember(
                $order['userId'],
                $order['data']['targetId'],
                $order['data']['duration'],
                $order['data']['unitType'],
                $order['id']
            );

            $level   = $this->getLevelService()->getLevel($vip['levelId']);
            $message = "您已经成功加入 {$level['name']} ，点击查看<a href='/vip/course/level/{$level['id']}' target='_blank'>{$level['name']}</a>课程";
        } elseif ($order['data']['buyType'] == 'renew') {
            $vip = $this->getVipService()->renewMember(
                $order['userId'],
                $order['data']['duration'],
                $order['data']['unitType'],
                $order['id']
            );

            $level   = $this->getLevelService()->getLevel($vip['levelId']);
            $message = "您的 {$level['name']} 已成功续费，当前的有效期至：".date('Y-m-d', $vip['deadline']);
        } elseif ($order['data']['buyType'] == 'upgrade') {
            $vip = $this->getVipService()->upgradeMember(
                $order['userId'],
                $order['data']['targetId'],
                $order['id']
            );

            $level   = $this->getLevelService()->getLevel($vip['levelId']);
            $message = "您已经升级到 {$level['name']} ，点击查看<a href='/vip/course/level/{$level['id']}' target='_blank'>{$level['name']}</a>课程";
        }

        $this->getNotificationService()->notify($order['userId'], 'default', $message);
    }

    public function getOrderBySn($sn)
    {
        return $this->getOrderService()->getOrderBySn($sn);
    }

    public function updateOrder($id, $fileds)
    {
        return $this->getOrderService()->updateOrder($id, $fileds);
    }

    public function getNote($targetId)
    {
        $vipLevel = $this->getLevelService()->getLevel($targetId);
        return str_replace(' ', '', strip_tags($vipLevel['description']));
    }

    public function getTitle($targetId)
    {
        $vipLevel = $this->getLevelService()->getLevel($targetId);
        return str_replace(' ', '', strip_tags($vipLevel['name']));
    }

    public function pay($payData)
    {
        return $this->getPayCenterService()->pay($payData);
    }

    public function cancelOrder($id, $message, $data)
    {
        return $this->getOrderService()->cancelOrder($id, $message, $data);
    }

    public function createPayRecord($id, $payData)
    {
        return $this->getOrderService()->createPayRecord($id, $payData);
    }

    public function generateOrderToken()
    {
        return 'c'.date('YmdHis', time()).mt_rand(10000, 99999);
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    public function getOrderInfoTemplate()
    {
        return "VipBundle:Vip:orderInfo";
    }

    public function isTargetExist($targetId)
    {
        $level = $this->getLevelService()->getLevel($targetId);

        if (empty($level) || $level['enabled'] == 0) {
            return false;
        }

        return true;
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    public function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User.NotificationService');
    }

    protected function getLevelService()
    {
        return ServiceKernel::instance()->createService("Vip:Vip.LevelService");
    }

    protected function getVipService()
    {
        return ServiceKernel::instance()->createService('Vip:Vip.VipService');
    }

    protected function getOrderService()
    {
        return ServiceKernel::instance()->createService('Order.OrderService');
    }

    protected function getPayCenterService()
    {
        return ServiceKernel::instance()->createService('PayCenter.PayCenterService');
    }
}
