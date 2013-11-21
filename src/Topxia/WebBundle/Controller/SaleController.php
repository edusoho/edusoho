<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Component\Payment\Payment;
use Symfony\Component\HttpFoundation\Response;

class SaleController extends BaseController
{

      
    public function codeCheckAction(Request $request)
    {
        $order =  $request->request->all();

        $code = $order['promoCode'];

        $courseId = $order['courseId'];
        
        $offsale = $this->getOffsaleService()->getOffsaleByCode($code);

        $result = $this->getOffsaleService()->isValiable($offsale,$courseId);

        if ("success" == $result) {
            $response = array('success' => true, 'message' => '感谢使用，该优惠码立减'.$offsale['reducePrice'].'元！');
        } else {
            $response = array('success' => false, 'message' => $result);
        }
         
        return $this->createJsonResponse($response);
    }


   


    private function getOffsaleService()
    {
        return $this->getServiceKernel()->createService('Sale.OffsaleService');
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Course.OrderService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

   

}