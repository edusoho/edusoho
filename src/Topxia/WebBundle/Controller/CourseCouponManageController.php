<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CourseCouponManageController extends BaseController
{

    public function indexAction(Request $request, $id)
    {   
        $conditions = $request->query->all();
    	$course = $this->getCourseService()->tryManageCourse($id);

    	$paginator = new Paginator(
            $request,
            $this->getOrderService()->searchCourseCouponsCount($conditions),
            20
        );

    	$courseCoupons = $this->getOrderService()->searchCourseCoupons(
    		$conditions, 
    		'latest', 
    		$paginator->getOffsetCount(),
        	$paginator->getPerPageCount()
        );

        return $this->render('TopxiaWebBundle:CourseCouponManage:index.html.twig', array(
            'course' => $course,
            'courseCoupons' => $courseCoupons,
            'paginator' =>$paginator
        ));
    }

    public function deleteAction(Request $request,$couponId) 
    {   
        $coupon = $this->getOrderService()->deleteCoupon($couponId);
        
        return $this->createJsonResponse(true);
    }

    public function createAction(Request $request, $id)
    {   

        $course = $this->getCourseService()->tryAdminCourse($id);

        if ($request->getMethod() == 'POST') {
            $number = $request->request->get('number');
            if ($number>100) {
                throw $this->createServiceException(sprintf('优惠码最多一次只能生成100个'));
            }
            $couponData = $request->request->all();
            
            for ($i = 0; $i < $number ; $i++) { 
                $this->getOrderService()->generateCoupon($couponData);
            }
        }
        return $this->render('TopxiaWebBundle:CourseCouponManage:create-modal.html.twig',array(
            'course'=>$course
        ));

    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

}