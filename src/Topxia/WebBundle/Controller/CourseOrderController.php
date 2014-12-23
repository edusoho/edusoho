<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Component\Payment\Payment;
use Topxia\WebBundle\Util\AvatarAlert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;

class CourseOrderController extends OrderController
{
    public $courseId = 0;

    public function buyAction(Request $request, $id)
    {
        $result = $this->getOrderInfo($id);

        return $this->render('TopxiaWebBundle:Order:order-create.html.twig', $result);
    }

    public function repayAction(Request $request)
    {
        $order = $this->getOrderService()->getOrder($request->query->get('orderId'));

        if (empty($order)) {
            return $this->createMessageResponse('error', '订单不存在!');
        }

        if ((time() - $order['createdTime']) > 40 * 3600 ) {
            return $this->createMessageResponse('error', '订单已过期，不能支付，请重新创建订单。');
        }

        $course = $this->getCourseService()->getCourse($order['targetId']);
        if (empty($course)) {
            return $this->createMessageResponse('error', '购买的课程不存在，请重新创建订单!');
        }

        $result = $this->getOrderInfo($order["targetId"]);
        $result["order"] = $order;

        return $this->render('TopxiaWebBundle:Order:order-create.html.twig', $result);
    }

    private function getOrderInfo($id)
    {
        $coinPayAmount = 0;
        $totalMoneyPrice = 0;
        $totalCoinPrice = 0;

        $totalPrice = 0;
        $coinPreferentialPrice = 0;
        $shouldPayMoney = 0;

        $course = $this->getCourseService()->getCourse($id);
        $userIds = array();
        $userIds = array_merge($userIds, $course['teacherIds']);
        $users = $this->getUserService()->findUsersByIds($userIds);
        $totalMoneyPrice = $course["price"];
        $totalCoinPrice = $course["coinPrice"];

        $coinSetting = $this->getSettingService()->get("coin");
        
        $cashRate = 10;
        if(array_key_exists("cash_rate", $coinSetting)) {
            $cashRate = $coinSetting["cash_rate"];
        }

        $coursePriceShowType = "Coin";
        if(array_key_exists("price_type", $coinSetting)) {
            $coursePriceShowType = $coinSetting["price_type"];
        }

        $user = $this->getCurrentUser();
        $account = $this->getCashAccountService()->getAccountByUserId($user["id"]);
        $accountCash = $account["cash"];

        $hasPayPassword = strlen($user['payPassword']) > 0;

        if($hasPayPassword 
            && array_key_exists("coin_enabled", $coinSetting) 
            && $coinSetting["coin_enabled"]) {
            if($coursePriceShowType == "RMB") {
                $totalPrice = $totalMoneyPrice;
                if($totalMoneyPrice*100 > $accountCash/$cashRate*100) {
                    $shouldPayMoney = $totalMoneyPrice - $accountCash/$cashRate;
                    $coinPayAmount = $accountCash;
                    $coinPreferentialPrice = $accountCash/$cashRate;
                } else {
                    $coinPayAmount = $totalMoneyPrice*$cashRate;
                    $coinPreferentialPrice = $totalMoneyPrice;
                }
            } else if ($coursePriceShowType == "Coin") {
                $totalPrice = $totalCoinPrice;
                if($totalCoinPrice*100 > $accountCash*100) {
                    $shouldPayMoney = ($totalCoinPrice - $accountCash)/$cashRate;
                    $coinPayAmount = $accountCash;
                } else {
                    $coinPayAmount = $totalCoinPrice;
                }
                
                $coinPreferentialPrice = $coinPayAmount;
            }
        }

        $shouldPayMoney = $totalMoneyPrice - $coinPreferentialPrice;

        $couponApp = $this->getAppService()->findInstallApp("Coupon");
        $vipApp = $this->getAppService()->findInstallApp("Vip");

        if(!empty($vipApp)) {
            $vip = $this->getVipService()->getMemberByUserId($user["id"]);
        }

        return array(
            'courses' => empty($course) ? null : array($course),
            'users' => empty($users) ? null : $users,
            'account' => $account,
            'couponApp' => $couponApp,
            'vipApp' => $vipApp,
            'cashRate' => $cashRate,

            'totalPrice' => $totalPrice,
            'shouldPayMoney' => $shouldPayMoney,
            'coinPayAmount' => $coinPayAmount,
            'coinPreferentialPrice' => $coinPreferentialPrice,

            'targetIds' => array($id),
            'targetTypes' => array("course"),
            'coursePriceShowType' => $coursePriceShowType,

            'vip' => empty($vip) ? null : array($vip),
            'payUrl' => 'course_order_pay',

            'hasPayPassword' => $hasPayPassword
        );
    }

    public function payAction(Request $request)
    {
        $fields = $request->request->all();
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，创建订单失败。');
        }

        if(!array_key_exists("targets", $fields)) {
            return $this->createMessageResponse('error', '订单中没有购买的内容，不能创建!');
        }
        $targets = $fields["targets"];
        
        $targetTypes = array();
        $targetIds = array();

        foreach ($targets as $key => $value) {
            $target = explode("-",$value);
            $targetTypes[] = $target[0];
            $targetIds[] = $target[1];
        }
        
        $coursePriceShowType = "RMB";
        $coinSetting = $this->getSettingService()->get("coin");
        if(array_key_exists("coin_enabled", $coinSetting) && $coinSetting["coin_enabled"] && array_key_exists("price_type", $coinSetting)) {
            $coursePriceShowType = $coinSetting["price_type"];
        }
        $cashRate = 1;
        if(array_key_exists("coin_enabled", $coinSetting) && $coinSetting["coin_enabled"] && array_key_exists("cash_rate", $coinSetting)) {
            $cashRate = $coinSetting["cash_rate"];
        }

        $totalPrice = 0;
        foreach ($targetTypes as $key => $value) {
            if("course" == $value) {
                $course = $this->getCourseService()->getCourse($targetIds[$key]);
                if($coursePriceShowType == "RMB") {
                    $totalPrice += $course["price"];
                } else if ($coursePriceShowType == "Coin") {
                    $totalPrice += $course["coinPrice"];
                }
            }
        }

        if($totalPrice != $fields["totalPrice"]) {
            return $this->createMessageResponse('error', '实际价格不匹配，不能创建订单!');
        }

        //虚拟币优惠价格
        $coinPayAmount = $fields["coinPayAmount"];
        if(!empty($coinPayAmount) && $coinPayAmount>0 && array_key_exists("coin_enabled", $coinSetting) && $coinSetting["coin_enabled"]) {
            $isRight = $this->getAuthService()->checkPayPassword($user["id"], $fields["payPassword"]);
            if(!$isRight)
                return $this->createMessageResponse('error', '支付密码不正确，创建订单失败。');
        }
        $coinPreferentialPrice = 0;
        if($coursePriceShowType == "RMB") {
            $coinPreferentialPrice = $coinPayAmount/$cashRate;
        } else if ($coursePriceShowType == "Coin") {
            $coinPreferentialPrice = $coinPayAmount;
        }

        $amount = $totalPrice - $coinPreferentialPrice;

        //优惠码优惠价格
        $couponApp = $this->getAppService()->findInstallApp("Coupon");
        if(!empty($couponApp) && $fields["couponCode"]) {
            $couponResult = $this->getCouponService()->checkCouponUseable($fields["couponCode"], "course", $targetIds[0], $amount);
            $afterRmbAmount = $couponResult["afterAmount"];
            if($coursePriceShowType == "RMB") {
                $amount = $amount - $couponResult["decreaseAmount"];
                $couponDiscount = $couponResult["decreaseAmount"];
            } else if ($coursePriceShowType == "Coin") {
                $amount = $amount - $couponResult["decreaseAmount"]*$cashRate;
                $couponDiscount = $couponResult["decreaseAmount"]*$cashRate;
            }
        }

        $amount = ceil($amount*100)/100;
        //价格比较
        if($amount != $fields["shouldPayMoney"]) {
            return $this->createMessageResponse('error', '支付价格不匹配，不能创建订单!');
        }

        $orderFileds = array(
            'priceType' => $coursePriceShowType,
            'totalPrice' => $totalPrice,
            'amount' => $amount,
            'coinRate' => $cashRate,
            'coinAmount' => $coinPayAmount,
            'userId' => $user["id"],
            'payment' => 'alipay',
            'courseId' => $targetIds[0],
            'coupon' => empty($couponResult) ? null : $fields["couponCode"],
            'couponDiscount' => empty($couponDiscount) ? null : $couponDiscount,
        );

        if(array_key_exists("orderId", $fields)){
            $order = $this->getOrderService()->getOrder($request->request->get('orderId'));

            if (empty($order)) {
                return $this->createMessageResponse('error', '订单不存在!');
            }
            $order = $this->getCourseOrderService()->updateOrder($order["id"], $orderFileds);
        } else {
            $order = $this->getCourseOrderService()->createOrder($orderFileds);
        }

        return $this->redirect($this->generateUrl('pay_center_show', array(
            'id' => $order['id']
        )));

    }

    public function refundAction(Request $request , $id)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($id);
        $user = $this->getCurrentUser();

        if (empty($member) or empty($member['orderId'])) {
            throw $this->createAccessDeniedException('您不是课程的学员或尚未购买该课程，不能退学。');
        }

        $order = $this->getOrderService()->getOrder($member['orderId']);
        if (empty($order)) {
            throw $this->createNotFoundException();
        }

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $reason = empty($data['reason']) ? array() : $data['reason'];
            $amount = empty($data['applyRefund']) ? 0 : null;

            $refund = $this->getCourseOrderService()->applyRefundOrder($member['orderId'], $amount, $reason, $this->container);

            return $this->createJsonResponse($refund);
        }

        $maxRefundDays = (int) $this->setting('refund.maxRefundDays', 0);
        $refundOverdue = (time() - $order['createdTime']) > ($maxRefundDays * 86400);

        return $this->render('TopxiaWebBundle:CourseOrder:refund-modal.html.twig', array(
            'course' => $course,
            'order' => $order,
            'maxRefundDays' => $maxRefundDays,
            'refundOverdue' => $refundOverdue,
        ));
    }

    public function cancelRefundAction(Request $request , $id)
    {
        $course = $this->getCourseService()->getCourse($id);
        if (empty($course)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();
        if (empty($user)) {
            throw $this->createAccessDeniedException();
        }

        $member = $this->getCourseService()->getCourseMember($course['id'], $user['id']);
        if (empty($member) or empty($member['orderId'])) {
            throw $this->createAccessDeniedException('您不是课程的学员或尚未购买该课程，不能取消退款。');
        }

        $this->getCourseOrderService()->cancelRefundOrder($member['orderId']);

        return $this->createJsonResponse(true);

    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    public function getCourseOrderService()
    {
        return $this->getServiceKernel()->createService('Course.CourseOrderService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getCashAccountService()
    {
        return $this->getServiceKernel()->createService('Cash.CashAccountService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getCouponService()
    {
        return $this->getServiceKernel()->createService('Coupon:Coupon.CouponService');
    }
}