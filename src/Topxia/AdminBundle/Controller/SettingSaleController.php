<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\Paginator;

class SettingSaleController extends BaseController
{
  

    public function saleSettingAction(Request $request)
    {
        $lswSetting = $this->getSettingService()->get('linksaleWebSetting', array());

        $default = array(
            'webCommissionType' => 'ratio',
            'webCommission' => '5',
            'webCommissionDay' => '30'
        );

        $lswSetting = array_merge($default, $lswSetting);

        if ($request->getMethod() == 'POST') {
            $lswSetting = $request->request->all();
            $this->getSettingService()->set('linksaleWebSetting', $lswSetting);
            $this->getLogService()->info('system', 'update_linksaleWebSetting', "更新网站链接推广设置", $lswSetting);
            $this->setFlashMessage('success','已更新网站链接推广设置');

            $this->getLinkSaleService()->updateCourseLinkSale4unCustomized($lswSetting['webCommissionType'],$lswSetting['webCommission'],$lswSetting['webCommissionDay'],0);
        }

        return $this->render('TopxiaAdminBundle:Sale:sale-setting.html.twig', array(
            'linksaleWebSetting' => $lswSetting
        ));
    }

  
    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
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

}