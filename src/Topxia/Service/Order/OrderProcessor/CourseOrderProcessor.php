<?php
namespace Topxia\Service\Order\OrderProcessor;

use Exception;
use Topxia\Common\NumberToolkit;
use Topxia\Service\Common\ServiceKernel;

class CourseOrderProcessor extends BaseProcessor implements OrderProcessor
{
    protected $router = "course_show";

    public function preCheck($targetId, $userId)
    {
        if ($this->getCourseService()->isCourseStudent($targetId, $userId)) {
            return array('error' => '已经是课程的学员了!');
        }

        $course = $this->getCourseService()->getCourse($targetId);

        if (!$course['buyable']) {
            return array('error' => '该课程不可购买，如有需要，请联系客服');
        }

        if ($course['status'] != 'published') {
            return array('error' => '不能加入未发布课程!');
        }

        if ($course["type"] == "live" && $course["studentNum"] >= $course["maxStudentNum"]) {
            return array('error' => '名额已满，不能加入!');
        }

        return array();
    }

    public function getOrderInfo($targetId, $fields)
    {
        $course = $this->getCourseService()->getCourse($targetId);

        if (empty($course)) {
            throw new Exception("找不到要购买课程!");
        }

        $users = $this->getUserService()->findUsersByIds($course['teacherIds']);

        list($coinEnable, $priceType, $cashRate) = $this->getCoinSetting();

        $totalPrice = 0;

        if (!$coinEnable) {
            $totalPrice = $course["price"];
            return array(
                'totalPrice' => $totalPrice,
                'targetId'   => $targetId,
                'targetType' => "course",

                'course'     => empty($course) ? null : $course,
                'users'      => $users
            );
        }

        if ($priceType == "Coin") {
            $coinSetting = $this->getSettingService()->get('coin');
            $coinEnable  = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"] == 1;
            $crshRate    = 1;

            if ($coinEnable && array_key_exists("cash_rate", $coinSetting)) {
                $cashRate = $coinSetting['cash_rate'];
            }

            $totalPrice = $course["price"] * $cashRate;
        } elseif ($priceType == "RMB") {
            $totalPrice = $course["price"];
            $maxCoin    = NumberToolkit::roundUp($course['maxRate'] * $course['originPrice'] / 100 * $cashRate);
        }

        list($totalPrice, $coinPayAmount, $account, $hasPayPassword) = $this->calculateCoinAmount($totalPrice, $priceType, $cashRate);

        if (!isset($maxCoin)) {
            $maxCoin = $coinPayAmount;
        }
        
        return array(
            'course'         => empty($course) ? null : $course,
            'users'          => empty($users) ? null : $users,
            'totalPrice'     => $totalPrice,
            'targetId'       => $targetId,
            'targetType'     => "course",
            'cashRate'       => $cashRate,
            'priceType'      => $priceType,
            'account'        => $account,
            'hasPayPassword' => $hasPayPassword,
            'coinPayAmount'  => $coinPayAmount,
            'maxCoin'        => $maxCoin
        );
    }

    public function shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields)
    {
        $totalPrice = $this->getTotalPrice($targetId, $priceType);

        $amount = $totalPrice;

//优惠码优惠价格

        if (isset($fields["couponCode"]) && trim($fields["couponCode"]) != "") {
            $couponResult = $this->afterCouponPay(
                $fields["couponCode"],
                'course',
                $targetId,
                $totalPrice,
                $priceType,
                $cashRate
            );

            if (isset($couponResult["useable"]) && $couponResult["useable"] == "yes" && isset($couponResult["afterAmount"])) {
                $amount = $couponResult["afterAmount"];
            } else {
                unset($couponResult);
            }
        }

//虚拟币优惠价格

        if (array_key_exists("coinPayAmount", $fields)) {
            $amount = $this->afterCoinPay(
                $coinEnabled,
                $priceType,
                $cashRate,
                $amount,
                $fields['coinPayAmount'],
                $fields["payPassword"]
            );
        }

        if ($priceType == "Coin") {
            $amount = $amount / $cashRate;
        }

        if ($amount < 0) {
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
        return $this->getCourseOrderService()->createOrder($orderInfo);
    }

    protected function getTotalPrice($targetId, $priceType)
    {
        $totalPrice = 0;
        $course     = $this->getCourseService()->getCourse($targetId);

        if ($priceType == "RMB") {
            $totalPrice = $course["price"];
        } elseif ($priceType == "Coin") {
            $coinSetting = $this->getSettingService()->get('coin');
            $coinEnable  = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"] == 1;
            $crshRate    = 1;
            if ($coinEnable && array_key_exists("cash_rate", $coinSetting)) {
                $cashRate = $coinSetting['cash_rate'];
            }

            $totalPrice = $course["price"] * $cashRate;
        }

        $totalPrice = (float) $totalPrice;
        return $totalPrice;
    }

    public function doPaySuccess($success, $order)
    {
        if (!$success) {
            return;
        }

        $this->getCourseOrderService()->doSuccessPayOrder($order['id']);

        return;
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
        $course = $this->getCourseService()->getCourse($targetId);
        return str_replace(' ', '', strip_tags($course['about']));
    }

    public function getTitle($targetId)
    {
        $course = $this->getCourseService()->getCourse($targetId);
        return str_replace(' ', '', strip_tags($course['title']));
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

    public function getOrderInfoTemplate()
    {
        return "TopxiaWebBundle:Course:orderInfo";
    }

    public function isTargetExist($targetId)
    {
        $course = $this->getCourseService()->getCourse($targetId);

        if (empty($course) || $course['status'] == 'closed') {
            return false;
        }

        return true;
    }

    protected function getCouponService()
    {
        return ServiceKernel::instance()->createService('Coupon.CouponService');
    }

    protected function getAppService()
    {
        return ServiceKernel::instance()->createService('CloudPlatform.AppService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getCourseOrderService()
    {
        return ServiceKernel::instance()->createService("Course.CourseOrderService");
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
