<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Component\Payment\Payment;

class OrderController extends BaseController
{
    public function createAction(Request $request)
    {

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();



            return $this->redirect($this->generateUrl('admin_user'));
        }

        $fields = $request->query->all();

        $totalPrice = 0;
        $shouldPayPrice = 0;
        $coinCash = 0;
        $coinPrice = 0;

        if(!empty($fields) && array_key_exists("targetType", $fields) && $fields["targetType"] == "course"){
            $course = $this->getCourseService()->getCourse($fields["targetId"]);
            $userIds = array();
            $userIds = array_merge($userIds, $course['teacherIds']);
            $users = $this->getUserService()->findUsersByIds($userIds);
            $totalPrice += $course["price"];
        }

        $user = $this->getCurrentUser();
        $cashAccount = $this->getCashService()->getAccountByUserId($user["id"]);

        $coinSetting = $this->getSettingService()->get("coin");
        if($totalPrice*100 > $cashAccount["cash"]/$coinSetting["cash_rate"]*100) {
            $shouldPayPrice = $totalPrice - $cashAccount["cash"]/$coinSetting["cash_rate"];
            $coinCash = $cashAccount["cash"];
        } else {
            $coinCash = $totalPrice*$coinSetting["cash_rate"];
        }

        $coinPrice = $coinCash/$coinSetting["cash_rate"];

        $couponApp = $this->getAppService()->findInstallApp("Coupon");
        $vipApp = $this->getAppService()->findInstallApp("Vip");

        if(!empty($vipApp)) {
            $vip = $this->getVipService()->getMemberByUserId($user["id"]);
        }

        return $this->render('TopxiaWebBundle:Order:order-create.html.twig', array(
            'courses' => empty($course) ? null : array($course),
            'users' => empty($users) ? null : $users,
            'cashAccount' => $cashAccount,
            'couponApp' => $couponApp,
            'vipApp' => $vipApp,
            'totalPrice' => $totalPrice,
            'shouldPayPrice' => $shouldPayPrice,
            'coinCash' => $coinCash,
            'coinPrice' => $coinPrice,
            'vip' => empty($vip) ? null : array($vip)
        ));
    }

    public function submitPayRequestAction(Request $request , $order, $requestParams)
    {
        $paymentRequest = $this->createPaymentRequest($order, $requestParams);
        
        return $this->render('TopxiaWebBundle:Order:submit-pay-request.html.twig', array(
            'form' => $paymentRequest->form(),
            'order' => $order,
        ));
    }

    public function resultNoticeAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:Order:resultNotice.html.twig');
    }

    public function couponCheckAction (Request $request, $type, $id)
    {
        if ($request->getMethod() == 'POST') {
            $code = $request->request->get('code');

            //判断coupon是否合法，是否存在跟是否过期跟是否可用于当前课程
            $course = $this->getCourseService()->getCourse($id);

            $couponInfo = $this->getCouponService()->checkCouponUseable($code, $type, $id, $course['price']);
            
            return $this->createJsonResponse($couponInfo);
        }
    }

    protected function doPayReturn(Request $request, $name, $successCallback = null)
    {
        $this->getLogService()->info('order', 'pay_result',  "{$name}页面跳转支付通知", $request->query->all());
        $response = $this->createPaymentResponse($name, $request->query->all());

        $payData = $response->getPayData();

        if ($payData['status'] == "waitBuyerConfirmGoods") {
            return $this->forward("TopxiaWebBundle:Order:resultNotice");
        }

        list($success, $order) = $this->getOrderService()->payOrder($payData);

        if ($order['status'] == 'paid' and $successCallback) {
            $successUrl = $successCallback($success, $order);
        }

        $goto = empty($successUrl) ? $this->generateUrl('homepage', array(), true) : $successUrl;
        return $this->redirect($goto);
    }

    protected function doPayNotify(Request $request, $name, $successCallback = null)
    {
        $this->getLogService()->info('order', 'pay_result', "{$name}服务器端支付通知", $request->request->all());
        $response = $this->createPaymentResponse($name, $request->request->all());

        $payData = $response->getPayData();
        try {
            list($success, $order) = $this->getOrderService()->payOrder($payData);
            if ($order['status'] == 'paid' and $successCallback) {
                $successCallback($success, $order);
            }

            return new Response('success');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function createPaymentRequest($order, $requestParams)
    {   
        $options = $this->getPaymentOptions($order['payment']);
        $request = Payment::createRequest($order['payment'], $options);

        $requestParams = array_merge($requestParams, array(
            'orderSn' => $order['sn'],
            'title' => $order['title'],
            'summary' => '',
            'amount' => $order['amount'],
        ));
        return $request->setParams($requestParams);
    }

    private function createPaymentResponse($name, $params)
    {
        $options = $this->getPaymentOptions($name);
        $response = Payment::createResponse($name, $options);

        return $response->setParams($params);
    }


    private function getPaymentOptions($payment)
    {
        $settings = $this->setting('payment');

        if (empty($settings)) {
            throw new \RuntimeException('支付参数尚未配置，请先配置。');
        }

        if (empty($settings['enabled'])) {
            throw new \RuntimeException("支付模块未开启，请先开启。");
        }

        if (empty($settings[$payment. '_enabled'])) {
            throw new \RuntimeException("支付模块({$payment})未开启，请先开启。");
        }

        if (empty($settings["{$payment}_key"]) or empty($settings["{$payment}_secret"])) {
            throw new \RuntimeException("支付模块({$payment})参数未设置，请先设置。");
        }

        $options = array(
            'key' => $settings["{$payment}_key"],
            'secret' => $settings["{$payment}_secret"],
            'type' => $settings["{$payment}_type"]
        );

        return $options;
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

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}