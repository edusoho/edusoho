<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
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
    		'latest', 
    		$paginator->getOffsetCount(),
        	$paginator->getPerPageCount()
        );

		return $this->render('TopxiaAdminBundle:Coupon:index.html.twig', array(
           'coupons' => $coupons,
           'paginator' =>$paginator
		));
	}

	public function deleteAction (Request $request,$id)
	{
        $result = $this->getCouponService()->deleteCoupon($id);
        return $this->createJsonResponse(true);
	}

	public function queryAction (Request $request)
	{
		return $this->render('TopxiaAdminBundle:Coupon:query.html.twig');
	}

	public function generateAction (Request $request)
	{
		return $this->render('TopxiaAdminBundle:Coupon:generate-modal.html.twig');
	}

    private function getCouponService()
    {
        return $this->getServiceKernel()->createService('Coupon.CouponService');
    }

}