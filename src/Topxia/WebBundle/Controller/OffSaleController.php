<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Component\Payment\Payment;
use Symfony\Component\HttpFoundation\Response;

class OffSaleController extends BaseController
{

      
    public function codeCheckAction(Request $request)
    {
        $order =  $request->request->all();

        $code = $order['promoCode'];

        $courseId = $order['courseId'];

        $course = $this->getCourseService()->getCourse($order['courseId']);

        $result = $this->getOffSaleService()->checkOffsaleUseable($code,'course',$courseId,$course['price']);

        if ("yes" == $result['useable']) {
           
            $response = array('success' => true, 'message' => '感谢使用，该优惠码立减'.$result['discount'].'元,现在购买只需'.$result['afterAmount'].'元！');
           
        } else {

            $response = array('success' => false, 'message' => $result['message']);

        }
         
        return $this->createJsonResponse($response);
    }


    private function getOffSaleService()
    {
        return $this->getServiceKernel()->createService('Sale.OffSaleService');
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
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