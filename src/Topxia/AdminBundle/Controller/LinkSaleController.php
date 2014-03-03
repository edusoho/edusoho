<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class LinkSaleController extends BaseController
{

	public function indexAction(Request $request){

		$conditions = $request->query->all();

        if (isset($conditions['nickName']) and !empty($conditions['nickName']) ){

            $partner = $this->getUserService()->getUserByNickname($conditions['nickName']);

            if (!empty($partner)) {

                $conditions['partnerId'] = $partner['id'];
               
             }else {
                $conditions['partnerId']=-1;
            }

        }


        $count = $this->getLinkSaleService()->searchLinkSaleCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $linksales = $this->getLinkSaleService()->searchLinkSales($conditions,'latest', $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        $mTookeens = ArrayToolkit::column($linksales,"mTookeen");

        $orders = $this->getOrderService()->findOrdersBymTookeens($mTookeens);

        $userIds = ArrayToolkit::column($orders,'userId');
 
        $users = $this->getUserService()->findUsersByIds($userIds);

        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);
        
        $orderss = $this->getOrderService()->findOrderssBymTookeens($mTookeens);


        $partnerIds = ArrayToolkit::column($linksales,"partnerId");

        $partners = $this->getUserService()->findUsersByIds($partnerIds);

        return $this->render('TopxiaAdminBundle:Sale:linksale-list.html.twig', array(
            'conditions' => $conditions,
            'linksales' => $linksales ,
            'orderss' => $orderss,
            'users' => $users,
            'profiles' => $profiles,
            'partners'=> $partners,
            'paginator' => $paginator
        ));

	}

    public function batchDeleteAction(Request $request)
    {
        $ids = $request->request->get('ids', array());
        $this->getLinkSaleService()->deleteLinkSales($ids);

        return $this->createJsonResponse(true);
    }


    public function settingAction(Request $request,$id){

         

         if('POST' == $request->getMethod()){

            $lsFields = $request->request->all();

            $user = $this->getCurrentUser();

            $this->getLinkSaleService()->updateLinkSale($id,$lsFields);
            
            return $this->redirect($this->generateUrl('admin_sale_linksale')); 
        }

        $linksale=$this->getLinkSaleService()->getLinkSale($id);

        return $this->render('TopxiaAdminBundle:Sale:linksale-setting-modal.html.twig',array('linksale'=>$linksale));
    }

   
    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Course.OrderService');
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