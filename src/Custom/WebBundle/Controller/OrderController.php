<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class OrderController extends BaseController
{


    public function verifyAction(Request $request)
    {
        
        return $this->render('CustomWebBundle:Order:order-verify.html.twig',array(
         
        ));
    }
    public function userVerifyAction(Request $request)
    {
        $user = array('name' =>'zhangsan' ,'age'=>18 );
        return $this->render('CustomWebBundle:Order:user-verify.html.twig',array(
            'user' => $user
        ));
    }
    

    private function getCartsService()
    {
        return $this->getServiceKernel()->createService('Custom:Carts.CartsService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}