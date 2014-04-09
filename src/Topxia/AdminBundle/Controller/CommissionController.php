<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class CommissionController extends BaseController
{

	public function indexAction(Request $request){

		$user = $this->getCurrentUser();

        $sort  = 'latest';

        $conditions = $request->query->all();

        if (isset($conditions['nickName']) and !empty($conditions['nickName'])){

            $saler = $this->getUserService()->getUserByNickname($conditions['nickName']);

            if (!empty($saler)) {

                $conditions['salerId'] = $saler['id'];
               
            }else {
                $conditions['salerId']=-1;
            }
        }


        $paginator = new Paginator(
            $this->get('request'),
            $this->getCommissionService()->searchCommissionCount($conditions)
            ,12
        );


        $commissions = $this->getCommissionService()->searchCommissions(
            $conditions, $sort,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $orderIds=ArrayToolkit::column($commissions,'orderId');

        $orders = $this->getOrderService()->findOrdersByIds($orderIds);

        $linksaleIds=ArrayToolkit::column($commissions,'saleId');

        $linksales = $this->getLinkSaleService()->findLinkSalesByIds($linksaleIds);

        $offsaleIds=ArrayToolkit::column($commissions,'saleId');

        $offsales = $this->getOffSaleService()->findOffSalesByIds($offsaleIds);

        $buyerIds=ArrayToolkit::column($commissions,'buyerId');

        $buyers = $this->getUserService()->findUsersByIds($buyerIds);

        $salerIds=ArrayToolkit::column($commissions,'salerId');

        $salers = $this->getUserService()->findUsersByIds($salerIds);
 
       
        return $this->render('TopxiaAdminBundle:Sale:commission-list.html.twig', array(
            'commissions'=>$commissions,
            'orders' => $orders,
            'linksales' => $linksales,
            'offsales' => $offsales,
            'buyers' => $buyers,
            'salers' => $salers,
            'paginator' => $paginator
        ));       

	}

   
    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getCommissionService()
    {
        return $this->getServiceKernel()->createService('Sale.CommissionService');
    }
     
    protected function getLinkSaleService()
    {
        return $this->getServiceKernel()->createService('Sale.LinkSaleService');
    }

    protected function getOffSaleService()
    {
        return $this->getServiceKernel()->createService('Sale.OffSaleService');
    }
    

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }
 
    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }


}