<?php

namespace AppBundle\Controller\AdminV2\Marketing;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\Distributor\Service\Impl\DistributorUserServiceImpl;
use Biz\Marketing\Util\MarketingUtils;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class DistributorController extends BaseController
{
    public function bootAction(Request $request)
    {
        return $this->render('admin-v2/marketing/distributor/boot.html.twig');
    }

    public function loginAction(Request $request)
    {
        $drpService = $this->getDistributorUserService()->getDrpService();
        if (!$drpService) {
            return $this->render('admin-v2/cloud-center/edu-cloud/not-access.html.twig', array('menu' => 'admin_v2_distributor_page'));
        }

        $form = MarketingUtils::generateLoginFormForCurrentUser(array(
            'settingService' => $this->getSettingService(),
            'webExtension' => $this->getWebExtension(),
            'request' => $request,
            'currentUser' => $this->getCurrentUser(),
            'drpService' => $drpService,
        ));

        return $this->render('admin-v2/marketing/distributor/index.html.twig', array(
            'form' => $form,
        ));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return DistributorUserServiceImpl
     */
    protected function getDistributorUserService()
    {
        return $this->createService('Distributor:DistributorUserService');
    }
}
