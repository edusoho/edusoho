<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Component\Payment\Payment;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;
use Topxia\Common\SmsToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\OrderController as BaseOrderController;

class OrderController extends BaseOrderController
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

        if('course'==$fields['targetType']){
            $course=$this->getCourseService()->loadCourse($fields['targetId']);
            if($course['maxStudentNum']<=$course['studentNum']){
                $this->setFlashMessage('danger', '该课程学员已满，无法加入');
                return $this->redirect($request->headers->get("referer"));
            }
        }

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
