<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class OffSaleController extends BaseController
{

	public function indexAction(Request $request){

		$conditions = $request->query->all();

        $count = $this->getOffSaleService()->searchOffSaleCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $offsales = $this->getOffSaleService()->searchOffSales($conditions,'latest', $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        $codes = ArrayToolkit::column($offsales,"promoCode");

        $orders = $this->getOrderService()->findOrdersByPromoCodes($codes);

        $userIds = ArrayToolkit::column($orders,'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);
        
        $orderss = $this->getOrderService()->findOrderssByPromoCodes($codes);

        return $this->render('TopxiaAdminBundle:Sale:index.html.twig', array(
            'conditions' => $conditions,
            'offsales' => $offsales ,
            'orderss' => $orderss,
            'users' => $users,
            'profiles' => $profiles,
            'paginator' => $paginator
        ));

	}

    public function createAction(Request $request)
    {
        if('POST' == $request->getMethod()){
            $offsetting = $request->request->all();

            $data = $request->request->all();
            $user = $this->getUserService()->getUserByNickname($data['partnerNname']);
            if (empty($user)) {
                throw $this->createNotFoundException("用户{$data['partnerNname']}不存在");
            }


            $this->getOffSaleService()->createOffSales($offsetting);
            
            return $this->redirect($this->generateUrl('admin_sale')); 
        }

        $offsetting = array(
            'id'=>0,
            'partnerName'=>'',
            'promoName'=>'',
            'adCommission'=>0,
            'reducePrice'=>0,
            'promoNum'=>1,
            'promoPrefix'=>'',
            'prodType'=>'课程',
            'prodId'=>'',
            'strvalidTime'=>'',
            'reuse'=>'不可以'
              );

        return $this->render('TopxiaAdminBundle:Sale:offsale-modal.html.twig',array('offsetting' => $offsetting));
    }

    public function prodCheckAction(Request $request)
    {
        $offsetting =  $request->request->all();

        $result = $this->getOffSaleService()->checkProd($offsetting);

        if ("true"==$result['hasProd']) {
            $response = array('success' => true, 'message' => $result['prodName']);
        } else {
            $response = array('success' => false, 'message' => $result['prodName']);
        }
         
        return $this->createJsonResponse($response);
    }

    public function batchDeleteAction(Request $request)
    {
        $ids = $request->request->get('ids', array());
        $this->getOffSaleService()->deleteOffSales($ids);

        return $this->createJsonResponse(true);
    }

    private function getOffSaleService()
    {
        return $this->getServiceKernel()->createService('Sale.OffSaleService');
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Course.OrderService');
    }

}