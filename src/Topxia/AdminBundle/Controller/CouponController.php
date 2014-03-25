<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class CouponController extends BaseController 
{	

	public function indexAction (Request $request)
	{   
        $conditions = $request->query->all();

        $paginator = new Paginator(
            $request,
            $this->getCouponService()->searchCouponsCount($conditions),
            20
        );

        $coupons = $this->getCouponService()->searchCoupons(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),  
            $paginator->getPerPageCount()
        );
        $batchs = $this->getCouponService()->findBatchsbyIds(ArrayToolkit::column($coupons, 'batchId'));
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($coupons, 'userId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($coupons, 'targetId'));

		return $this->render('TopxiaAdminBundle:Coupon:query.html.twig', array(
            'coupons' => $coupons,
            'paginator' => $paginator,
            'batchs' => $batchs,
            'users' => $users,
            'courses' =>$courses  
        ));
	}

    private function getCouponService()
    {
        return $this->getServiceKernel()->createService('Coupon.CouponService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

}