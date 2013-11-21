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
        $code = $request->query->get('value');
        
        $offsale = $this->getOffsaleService()->isCodeAvaliable($code);
        if ($offsale) {
            $response = array('success' => true, 'message' => '感谢使用，立减'.$offsale['reducePrice'].'元！');
        } else {
            $response = array('success' => false, 'message' => '该优惠码不存在，请重新输入');
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