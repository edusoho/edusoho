<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;


/*
 * @todo rename CouponBatchController
 */
class CouponController extends BaseController 
{	
	public function indexAction (Request $request)
	{	
        $conditions = $request->query->all();

    	$paginator = new Paginator(
            $request,
            $this->getCouponService()->searchBatchsCount($conditions),
            20
        );

    	$batchs = $this->getCouponService()->searchBatchs(
    		$conditions, 
    		'latest', 
    		$paginator->getOffsetCount(),
        	$paginator->getPerPageCount()
        );

		return $this->render('TopxiaAdminBundle:Coupon:index.html.twig', array(
           'batchs' => $batchs,
           'paginator' =>$paginator
		));
	}

	public function deleteAction (Request $request,$id)
	{
        $result = $this->getCouponService()->deleteBatch($id);
        return $this->createJsonResponse(true);
	}

    /* AdminBundle->CourseController->chooserAction  */
    public function courseAction (Request $request)
    {   
        $conditions = $request->query->all();

        $count = $this->getCourseService()->searchCourseCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $courses = $this->getCourseService()->searchCourses($conditions, null, $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        return $this->render('TopxiaAdminBundle:Coupon:course-modal.html.twig', array(
            'conditions' => $conditions,
            'courses' => $courses ,
            'users' => $users,
            'categories' => $categories,
            'paginator' => $paginator
        ));
    }

    /* AdminBundle->CouponController->indexAction  */
	public function queryAction (Request $request)
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

	public function checkPrefixAction(Request $request)
	{
		$prefix = $request->query->get('value');
		$result = $this->getCouponService()->checkPrefix($prefix);
        if ($result == true) {
            $response = array('success' => true, 'message' => '该前缀可以使用');
        } else {
            $response = array('success' => false, 'message' => '该前缀已存在');
        }
        return $this->createJsonResponse($response);
	}

	public function generateAction (Request $request)
	{   
        if ('POST' == $request->getMethod()) {
            $couponData = $request->request->all();
            if ($couponData['type'] == 'minus') {
                $couponData['rate'] = $couponData['minus-rate'];
                unset($couponData['minus-rate']);
                unset($couponData['discount-rate']);
            } else {
                $couponData['rate'] = $couponData['discount-rate'];
                unset($couponData['minus-rate']);
                unset($couponData['discount-rate']);
            }

            if ($couponData['targetType'] == 'course')
            {
                $couponData['targetId'] = $couponData['courseId'];
                unset($couponData['courseId']);
            }

            $batch = $this->getCouponService()->generateCoupon($couponData);

            return $this->redirect($this->generateUrl('admin_coupon'));
        }
		return $this->render('TopxiaAdminBundle:Coupon:generate.html.twig');
	}

    public function exportCsvAction(Request $request,$batchId)
    {
        $batch = $this->getCouponService()->getBatch($batchId);

        // findCouponsByBacthId($batchId, $start, $limit);
        $coupons = $this->getCouponService()->searchCoupons(
            array('batchId' => $batchId),
            'latest',
            1,
            $batch['generatedNum']
        );

        $str = "批次,有效期至,优惠码,状态"."\r\n";

        $statusNames = array('unused' => '未使用', 'used' => '已使用');

        $coupons = array_map(function($coupon) {
            $coupon['status'] = $statusNames[$coupon['status']];
            $export_coupon['batchId']  = $coupon['batchId'];
            $export_coupon['deadline'] = date('Y-m-d',$coupon['deadline']);
            $export_coupon['code']   = $coupon['code'];
            $export_coupon['status'] = $coupon['status'];
            return implode(',', $export_coupon);
        }, $coupons);

        $str .= implode("\r\n", $coupons);

        $filename = "couponBatch-".$batchId."-".date("YmdHi").".csv";

        $this->getLogService()->info('couponBatch_export', 'export', "导出了批次为{$batchId}的优惠码");

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

    private function createExporteCSVResponse(array $header, array $data, $outputFilename)
    {
        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$outputFilename.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

    /** detail  **/
    public function couponShowAction(Request $request, $batchId)
    {   
        $count = $this->getCouponService()->searchCouponsCount(array('batchId' => $batchId));

        $batch = $this->getCouponService()->getBatch($batchId);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $coupons = $this->getCouponService()->searchCoupons(
            array('batchId' => $batchId),
            'latest',
            $paginator->getOffsetCount(),  
            $paginator->getPerPageCount()
        );
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($coupons, 'userId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($coupons, 'targetId'));

        return $this->render('TopxiaAdminBundle:Coupon:coupon-modal.html.twig', array(
            'coupons' => $coupons,
            'batch' => $batch,
            'paginator' => $paginator,
            'users' => $users,
            'courses' => $courses
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