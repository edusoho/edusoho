<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\OrderController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;
class CustomOrderController extends OrderController
{
    public function couponCheckAction (Request $request, $type, $id)
    {   
        $user=$this->getCurrentUser();
        if ($request->getMethod() == 'POST') {
            $code = $request->request->get('code');

            //判断coupon是否合法，是否存在跟是否过期跟是否可用于当前课程
            $course = $this->getCourseService()->getCourse($id);

            $couponInfo = $this->getCouponService()->checkCouponUseable($code, $type, $id, $course['price']);
            
            $vip=$this->getVipService()->getMemberByUserId($user->id);
            $level=array();

            if($vip){
     
            $level=$this->getLevelService()->getLevel($vip['levelId']);

                if($level && $this->getVipService()->checkUserInMemberLevel($user->id,$vip['levelId'])=="ok"){
                    
                    $amount=$couponInfo['afterAmount'];
                    $couponInfo['afterAmount']=$couponInfo['afterAmount']*0.1*$level['courseDiscount'];

                    $couponInfo['afterAmount']=sprintf("%.2f", $couponInfo['afterAmount']);

                    $cut=$amount-$couponInfo['afterAmount'];

                    $couponInfo['decreaseAmount']=($couponInfo['decreaseAmount']+$cut);

                }
            }
            
            return $this->createJsonResponse($couponInfo);
        }
    }

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

        #如果是课程结算，需要判断该用户是否是会员，是否可以享受课程打折
        if($targetType  =='course' ){
            $orderInfo = $this->setShowVipDiscount($orderInfo,$currentUser);
          
        }


        return $this->render('CustomWebBundle:Order:order-create.html.twig', $orderInfo);
    }


    public function createAction(Request $request)
    {
        $fields = $request->request->all(); 
        var_dump( $fields);
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

             var_dump($amount, $totalPrice);

             //如果是课程，则根据用户是否是VIP重新教研支付金额
            if($targetType  =='course' ){
               list($amount, $totalPrice) = $this-> setPayVipDiscount($user, $fields, $totalPrice);
            }


            //价格比较
            if(intval($totalPrice*100) != intval($fields["totalPrice"]*100)) {
                
                return $this->createMessageResponse('error', "实际价格不匹配，不能创建订单!");
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


    protected function setPayVipDiscount($user, $fields, $totalPrice){
         $vip=$this->getVipService()->getMemberByUserId($user["id"]);
         $vipPrice= "";

         if($vip){
            $level=$this->getLevelService()->getLevel($vip['levelId']);
           
            if($level && $this->getVipService()->checkUserInMemberLevel($user["id"],$vip['levelId'])=="ok"){
               
                $status=$this->getVipService()->checkUserInMemberLevel($user["id"],$vip['levelId']);
               
                $vipPrice=$totalPrice*0.1*$level['courseDiscount'];
                $vipPrice=sprintf("%.2f", $vipPrice);
                $totalPrice = $vipPrice;
            }
        }
        $amount = round($totalPrice*1000 - $fields['coinPayAmount']*1000)/1000;

        return array( $amount,   $totalPrice );
    }

    protected function setShowVipDiscount($orderInfo,$currentUser){
        $vip=$this->getVipService()->getMemberByUserId($currentUser["id"]);
        $course =$orderInfo['courses'][0];
        $level=array();
        $vipPrice=$course['price'];
        $status="false";

        if($vip){

            $level=$this->getLevelService()->getLevel($vip['levelId']);
             $orderInfo['level'] = $level;
            if($level && $this->getVipService()->checkUserInMemberLevel($currentUser["id"],$vip['levelId'])=="ok"){
               
                $status=$this->getVipService()->checkUserInMemberLevel($currentUser["id"],$vip['levelId']);
                $course['status'] = $status;

                $vipPrice=$course['price']*0.1*$level['courseDiscount'];
                $vipPrice=sprintf("%.2f", $vipPrice);
                $course['vipPrice'] =  $vipPrice;

                if(isset($course['coinPrice'])){
                    $course['vipCoinPrice']=$course['coinPrice']*0.1*$level['courseDiscount'];
                    $course['vipCoinPrice']=sprintf("%.2f", $course['vipCoinPrice']);
                }
            }
           
           $orderInfo['course'] = $course;
        }
        return  $orderInfo;
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    } 

    protected function getLevelService()
    {   
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }
}