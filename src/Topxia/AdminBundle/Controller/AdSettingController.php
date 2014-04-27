<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Component\Payment\Payment;
use Symfony\Component\HttpFoundation\Response;

class AdSettingController extends BaseController
{
    
    public function listAction(Request $request)
    {

        $conditions = $request->query->all();

        if (isset($conditions['nickName']) and !empty($conditions['nickName']) ){

            $partner = $this->getUserService()->getUserByNickname($conditions['nickName']);

            if (!empty($partner)) {

                $conditions['partnerId'] = $partner['id'];
               
             }else {
                $conditions['partnerId']=-1;
            }

        }


        $count = $this->getAdSettingService()->searchSettingCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $settings = $this->getAdSettingService()->searchSettings($conditions,'latest', $paginator->getOffsetCount(),  $paginator->getPerPageCount());


        return $this->render('TopxiaAdminBundle:Sale:ad-setting-list.html.twig', array(
            'conditions' => $conditions,
            'settings' => $settings ,
            'paginator' => $paginator
        ));
    }


    public function createAction(Request $request)
    {


        if('POST' == $request->getMethod()){

            $offsetting = $request->request->all();

            $user = $this->getCurrentUser();

          
            $partner = $this->getUserService()->getUserByNickname($offsetting['partnerName']);

            if (empty($partner)) {
                throw $this->createNotFoundException("用户{$offsetting['partnerName']}不存在");
            }

            $offsetting['partnerId'] = $partner['id'];
            $offsetting['managerId'] = $user['id'];

            $this->getOffSaleService()->createOffSales($offsetting);
            
            return $this->redirect($this->generateUrl('admin_sale_offsale')); 
        }

        $offsetting = array(
            'id'=>0,
            'partnerName'=>'',
            'promoName'=>'',
            'adCommissionType'=>'ratio',
            'adCommission'=>0,
            'adCommissionDay'=>0,
            'reducePrice'=>0,
            'promoNum'=>1,
            'promoPrefix'=>'',
            'prodType'=>'course',
            'prodId'=>'',
            'strvalidTime'=>'',
            'reuse'=>'不可以'
              );

        return $this->render('TopxiaAdminBundle:Sale:ad-setting-create-modal.html.twig',array('offsetting' => $offsetting));
   

    }





    private function getAdSettingService()
    {
        return $this->getServiceKernel()->createService('Ad.SettingService');
    }

   

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

   

}