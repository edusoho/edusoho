<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\SmsToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\JoinPointToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;

class OrderController extends BaseController
{
    public function showAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isLogin()) {
            return $this->redirect($this->generateUrl('login'));
        }

        $targetType = $request->query->get('targetType');
        $targetId   = $request->query->get('targetId');
        $orderType  = JoinPointToolkit::load('order');
        if (empty($targetType)
            || empty($targetId)
            || !array_key_exists($targetType, $orderType)) {
            return $this->createMessageResponse('error', '参数不正确');
        }

        $processor = OrderProcessorFactory::create($targetType);
        $checkInfo = $processor->preCheck($targetId, $currentUser['id']);

        if (isset($checkInfo['error'])) {
            return $this->createMessageResponse('error', $checkInfo['error']);
        }

        $fields    = $request->query->all();
        $orderInfo = $processor->getOrderInfo($targetId, $fields);

        if (((float) $orderInfo["totalPrice"]) == 0) {
            $formData               = array();
            $formData['userId']     = $currentUser["id"];
            $formData["targetId"]   = $fields["targetId"];
            $formData["targetType"] = $fields["targetType"];
            $formData['amount']     = 0;
            $formData['totalPrice'] = 0;
            $coinSetting            = $this->setting("coin");
            $formData['priceType']  = empty($coinSetting["priceType"]) ? 'RMB' : $coinSetting["priceType"];
            $formData['coinRate']   = empty($coinSetting["coinRate"]) ? 1 : $coinSetting["coinRate"];
            $formData['coinAmount'] = 0;
            $formData['payment']    = 'alipay';
            $order                  = $processor->createOrder($formData, $fields);

            if ($order['status'] == 'paid') {
                return $this->redirect($processor->callbackUrl($order, $this->container));
            }
        }

        // $couponApp = $this->getAppService()->findInstallApp("Coupon");

        // // if (isset($couponApp["version"]) && version_compare("1.0.5", $couponApp["version"], "<=")) {
        // $orderInfo["showCoupon"] = true;
        // // }

        $verifiedMobile = '';

        if ((isset($currentUser['verifiedMobile'])) && (strlen($currentUser['verifiedMobile']) > 0)) {
            $verifiedMobile = $currentUser['verifiedMobile'];
        }

        $orderInfo['verifiedMobile'] = $verifiedMobile;
        $orderInfo['hasPassword']    = strlen($currentUser['password']) > 0;
        return $this->render('TopxiaWebBundle:Order:order-create.html.twig', $orderInfo);
    }

    public function smsVerificationAction(Request $request)
    {
        $currentUser    = $this->getCurrentUser();
        $verifiedMobile = '';

        if ((isset($currentUser['verifiedMobile'])) && (strlen($currentUser['verifiedMobile']) > 0)) {
            $verifiedMobile = $currentUser['verifiedMobile'];
        }

        return $this->render('TopxiaWebBundle:Order:order-sms-modal.html.twig', array(
            'verifiedMobile' => $verifiedMobile
        ));
    }

    public function createAction(Request $request)
    {
        $fields = $request->request->all();

        if (isset($fields['coinPayAmount']) && $fields['coinPayAmount'] > 0) {
            $scenario = "sms_user_pay";

            if ($this->setting('cloud_sms.sms_enabled') == '1' && $this->setting("cloud_sms.{$scenario}") == 'on') {
                list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, $scenario);

                if (!$result) {
                    return $this->createMessageResponse('error', '短信验证失败。');
                }
            }
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，创建订单失败。');
        }

        if (!array_key_exists("targetId", $fields) || !array_key_exists("targetType", $fields)) {
            return $this->createMessageResponse('error', '订单中没有购买的内容，不能创建!');
        }

        $targetType  = $fields["targetType"];
        $targetId    = $fields["targetId"];
        $maxRate     = $fields["maxRate"];
        $priceType   = "RMB";
        $coinSetting = $this->setting("coin");
        $coinEnabled = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"];

        if ($coinEnabled && isset($coinSetting["price_type"])) {
            $priceType = $coinSetting["price_type"];
        }

        $cashRate = 1;

        if ($coinEnabled && isset($coinSetting["cash_rate"])) {
            $cashRate = $coinSetting["cash_rate"];
        }

        $processor = OrderProcessorFactory::create($targetType);

        try {
            if (isset($fields["couponCode"]) && $fields["couponCode"] == "请输入优惠码") {
                $fields["couponCode"] = "";
            }

            list($amount, $totalPrice, $couponResult) = $processor->shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields);

            $amount         = (string) ((float) $amount);
            $shouldPayMoney = (string) ((float) $fields["shouldPayMoney"]);
            //价格比较

            if (intval($totalPrice * 100) != intval($fields["totalPrice"] * 100)) {
                $this->createMessageResponse('error', "实际价格不匹配，不能创建订单!");
            }

            //价格比较

            if (intval($amount * 100) != intval($shouldPayMoney * 100)) {
                return $this->createMessageResponse('error', '支付价格不匹配，不能创建订单!');
            }

            //虚拟币抵扣率比较

            if (isset($fields['coinPayAmount']) && (intval((float) $fields['coinPayAmount'] * 100) > intval($totalPrice * $maxRate * 100))) {
                return $this->createMessageResponse('error', '虚拟币抵扣超出限定，不能创建订单!');
            }

            if (isset($couponResult["useable"]) && $couponResult["useable"] == "yes") {
                $coupon         = $fields["couponCode"];
                $couponDiscount = $couponResult["decreaseAmount"];
            }

            $orderFileds = array(
                'priceType'      => $priceType,
                'totalPrice'     => $totalPrice,
                'amount'         => $amount,
                'coinRate'       => $cashRate,
                'coinAmount'     => empty($fields["coinPayAmount"]) ? 0 : $fields["coinPayAmount"],
                'userId'         => $user["id"],
                'payment'        => 'none',
                'targetId'       => $targetId,
                'coupon'         => empty($coupon) ? '' : $coupon,
                'couponDiscount' => empty($couponDiscount) ? 0 : $couponDiscount
            );

            $order = $processor->createOrder($orderFileds, $fields);
            if ($order["status"] == "paid") {
                return $this->redirect($processor->callbackUrl($order, $this->container));
            }

            return $this->redirect($this->generateUrl('pay_center_show', array(
                'sn'         => $order['sn'],
                'targetType' => $order['targetType']
            )));
        } catch (\Exception $e) {
            return $this->createMessageResponse('error', $e->getMessage());
        }
    }

    public function detailAction(Request $request, $id)
    {
        $order = $this->getOrderService()->getOrder($id);

        preg_match('/管理员添加/', $order['title'], $order['edit']);
        $user = $this->getUserService()->getUser($order['userId']);

        $orderLogs = $this->getOrderService()->findOrderLogs($order['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'userId'));

        return $this->render('TopxiaWebBundle:Order:detail-modal.html.twig', array(
            'order'     => $order,
            'user'      => $user,
            'orderLogs' => $orderLogs,
            'users'     => $users
        ));
    }

    public function couponCheckAction(Request $request, $type, $id)
    {
        if ($request->getMethod() == 'POST') {
            $code = $request->request->get('code');

            if (!in_array($type, array('course', 'vip', 'classroom'))) {
                throw new \RuntimeException('优惠券不支持的购买项目。');
            }

            $price = $request->request->get('amount');

            $couponInfo = $this->getCouponService()->checkCouponUseable($code, $type, $id, $price);
            return $this->createJsonResponse($couponInfo);
        }
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getCashService()
    {
        return $this->getServiceKernel()->createService('Cash.CashService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getCouponService()
    {
        return $this->getServiceKernel()->createService('Coupon.CouponService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}
