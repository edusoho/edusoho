<?php

namespace AppBundle\Controller\AdminV2\Marketing;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\Distributor\Service\Impl\DistributorUserServiceImpl;
use Biz\Marketing\Util\MarketingUtils;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class DistributorController extends BaseController
{
    public function loginAction(Request $request)
    {
        $form = MarketingUtils::generateLoginFormForCurrentUser(array(
            'settingService' => $this->getSettingService(),
            'webExtension' => $this->getWebExtension(),
            'request' => $request,
            'currentUser' => $this->getCurrentUser(),
            'drpService' => $this->getDistributorUserService()->getDrpService(),
        ));

        return $this->render('admin/marketing/login.html.twig', array(
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
