<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Component\Payment\Payment;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;
use Topxia\Common\SmsToolkit;

class OrderController extends BaseController
{
    public function showAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isLogin()) {
            return $this->redirect($this->generateUrl('login'));
        }

        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');

        if(empty($targetType) || empty($targetId) || !in_array($targetType, array("course", "vip","classroom")) ) {
            return $this->createMessageResponse('error', '参数不正确');
        }

        $processor = OrderProcessorFactory::create($targetType);
        $checkInfo = $processor->preCheck($targetId, $currentUser['id']);
        if (isset($checkInfo['error'])) {
            return $this->createMessageResponse('error', $checkInfo['error']);
        }

        $fields = $request->query->all();
        $orderInfo = $processor->getOrderInfo($targetId, $fields);

        if (((float)$orderInfo["totalPrice"]) == 0) {
            $formData = array();
            $formData['userId'] = $currentUser["id"];
            $formData["targetId"] = $fields["targetId"];
            $formData["targetType"] = $fields["targetType"];
            $formData['amount'] = 0;
            $formData['totalPrice'] = 0;
            $coinSetting = $this->setting("coin");
            $formData['priceType'] = empty($coinSetting["priceType"]) ? 'RMB' : $coinSetting["priceType"];
            $formData['coinRate'] = empty($coinSetting["coinRate"]) ? 1 : $coinSetting["coinRate"];
            $formData['coinAmount'] = 0;
            $formData['payment'] = 'alipay';
            $order = $processor->createOrder($formData, $fields);

            if ($order['status'] == 'paid') {
                return $this->redirect($this->generateUrl($processor->getRouter(), array('id' => $order['targetId'])));
            }
        }

        $couponApp = $this->getAppService()->findInstallApp("Coupon");
        if (isset($couponApp["version"]) && version_compare("1.0.5", $couponApp["version"], "<=")) {
            $orderInfo["showCoupon"] = true;
        }

        $verifiedMobile = '';
        if ( (isset($currentUser['verifiedMobile'])) && (strlen($currentUser['verifiedMobile'])>0) ){
            $verifiedMobile = $currentUser['verifiedMobile'];
        }
        $orderInfo['verifiedMobile'] = $verifiedMobile;

        return $this->render('TopxiaWebBundle:Order:order-create.html.twig', $orderInfo);

    }

    public function smsVerificationAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $verifiedMobile = '';
        if ( (isset($currentUser['verifiedMobile'])) && (strlen($currentUser['verifiedMobile'])>0) ){
            $verifiedMobile = $currentUser['verifiedMobile'];
        }
        return $this->render('TopxiaWebBundle:Order:order-sms-modal.html.twig', array(
            'verifiedMobile' => $verifiedMobile,
        ));
    }

    public function createAction(Request $request)
    {
        $fields = $request->request->all(); 

        if (isset($fields['coinPayAmount']) && $fields['coinPayAmount']>0){
            $eduCloudService = $this->getEduCloudService();
            $scenario = "sms_user_pay";
            if ($eduCloudService->getCloudSmsKey('sms_enabled') == '1'  && $eduCloudService->getCloudSmsKey($scenario) == 'on') {
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

        $targetType = $fields["targetType"];
        $targetId = $fields["targetId"];

        $priceType = "RMB";
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
            if(isset($fields["couponCode"]) && $fields["couponCode"]=="请输入优惠码"){
                $fields["couponCode"] = "";
            } 

            list($amount, $totalPrice, $couponResult) = $processor->shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields);
            $amount = (string) ((float) $amount);
            $shouldPayMoney = (string) ((float) $fields["shouldPayMoney"]);

            //价格比较
            if(intval($totalPrice*100) != intval($fields["totalPrice"]*100)) {
                $this->createMessageResponse('error', "实际价格不匹配，不能创建订单!");
            }

            //价格比较
            if(intval($amount*100) != intval($shouldPayMoney*100)) {
                return $this->createMessageResponse('error', '支付价格不匹配，不能创建订单!');
            }

            if (isset($couponResult["useable"]) && $couponResult["useable"] == "yes") {
                $coupon = $fields["couponCode"];
                $couponDiscount = $couponResult["decreaseAmount"];
            }

            $orderFileds = array(
                'priceType' => $priceType,
                'totalPrice' => $totalPrice,
                'amount' => $amount,
                'coinRate' => $cashRate,
                'coinAmount' => empty($fields["coinPayAmount"]) ? 0 : $fields["coinPayAmount"],
                'userId' => $user["id"],
                'payment' => 'alipay',
                'targetId' => $targetId,
                'coupon' => empty($coupon) ? '' : $coupon,
                'couponDiscount' => empty($couponDiscount) ? 0 : $couponDiscount
            );

            $order = $processor->createOrder($orderFileds, $fields);

            if($order["status"] == "paid") {
                return $this->redirect($this->generateUrl($processor->getRouter(), array('id' => $order["targetId"])));
            }

            return $this->redirect($this->generateUrl('pay_center_show', array(
                'sn' => $order['sn']
            )));
        } catch (\Exception $e) {
            return $this->createMessageResponse('error', $e->getMessage());
        }

    }

    public function couponCheckAction (Request $request, $type, $id)
    {
        if ($request->getMethod() == 'POST') {
            $code = $request->request->get('code');

            if (!in_array($type, array('course', 'vip', 'classroom'))) {
                throw new \RuntimeException('优惠码不支持的购买项目。');
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
        return $this->getServiceKernel()->createService('Coupon:Coupon.CouponService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getEduCloudService()
    {
        return $this->getServiceKernel()->createService('EduCloud.EduCloudService');
    }   
}
