<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class CouponBatchController extends BaseController 
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
    		array('createdTime', 'DESC'), 
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

	public function checkPrefixAction(Request $request)
	{
		$prefix = $request->query->get('value');
		$result = $this->getCouponService()->checkBatchPrefix($prefix);
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

        $coupons = $this->getCouponService()->findCouponsByBatchId(
            $batchId,
            1,
            $batch['generatedNum']
        );

        $coupons = array_map(function($coupon) {
            $export_coupon['batchId']  = $coupon['batchId'];
            $export_coupon['deadline'] = date('Y-m-d',$coupon['deadline']);
            $export_coupon['code']   = $coupon['code'];
            if ($coupon['status'] == 'unused') {
                $export_coupon['status'] = '未使用';
            } else {
                $export_coupon['status'] = '已使用'; 
            }
            return implode(',', $export_coupon);
        }, $coupons);

        $exportFilename = "couponBatch-".$batchId."-".date("YmdHi").".csv";

        $titles = array("批次","有效期至","优惠码","状态");

        $exportFile = $this->createExporteCSVResponse($titles, $coupons, $exportFilename);

        return $exportFile;
    }

    private function createExporteCSVResponse(array $header, array $data, $outputFilename)
    {   
        $header = implode(',', $header);

        $str = $header."\r\n";

        $str .= implode("\r\n", $data);

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$outputFilename.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

    public function detailAction(Request $request, $batchId)
    {   
        $count = $this->getCouponService()->searchCouponsCount(array('batchId' => $batchId));

        $batch = $this->getCouponService()->getBatch($batchId);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $coupons = $this->getCouponService()->searchCoupons(
            array('batchId' => $batchId),
            array('createdTime', 'DESC'),
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