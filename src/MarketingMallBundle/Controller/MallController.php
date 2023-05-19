<?php

namespace MarketingMallBundle\Controller;

use AppBundle\Controller\AdminV2\BaseController;
use MarketingMallBundle\Biz\Mall\Service\MallService;

class MallController extends BaseController
{
    public function indexAction()
    {
        if (!$this->getMallService()->isInit()) {
            return $this->createNotFoundException();
        }
        $mallSettings = $this->getSettingService()->get('marketing_mall', []);
        $mallUrl = 'https://' . $this->container->getParameter('marketing_mall_url') . '/custom-h5/?tab=home&schoolCode=' . $mallSettings['code'];

        return $this->redirect($mallUrl);
    }

    /**
     * @return MallService
     */
    protected function getMallService()
    {
        return $this->createService('Mall:MallService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
