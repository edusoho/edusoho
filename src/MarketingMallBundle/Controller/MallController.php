<?php

namespace MarketingMallBundle\Controller;

use AppBundle\Common\UrlToolkit;
use AppBundle\Controller\BaseController;
use MarketingMallBundle\Biz\Mall\Service\MallService;
use Symfony\Component\HttpFoundation\Request;

class MallController extends BaseController
{
    public function indexAction(Request $request)
    {
        if (!$this->getMallService()->isInit()) {
            return $this->createNotFoundException();
        }
        $mallSettings = $this->getSettingService()->get('marketing_mall', []);
        $mallUrl = $request->getScheme().'://' . UrlToolkit::ltrimHttpProtocol($this->container->getParameter('marketing_mall_url')) . '/custom-h5/?tab=home&schoolCode=' . $mallSettings['code'];

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
