<?php

namespace MarketingMallBundle\Handler;

use MarketingMallBundle\Biz\Mall\Service\MallService;
use MarketingMallBundle\Client\MarketingMallClient;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class LogoutSuccessHandler extends \AppBundle\Handler\LogoutSuccessHandler
{
    public function onLogoutSuccess(Request $request)
    {
        if ($this->getMallService()->isInit()) {
            $user = ServiceKernel::instance()->getCurrentUser();
            $client = new MarketingMallClient(ServiceKernel::instance()->getBiz());
            $client->logout([
                'userId' => $user->getId(),
                'sessionId' => $request->getSession()->getId(),
            ]);
        }

        return parent::onLogoutSuccess($request);
    }

    /**
     * @return MallService
     */
    protected function getMallService()
    {
        return ServiceKernel::instance()->createService('Mall:MallService');
    }
}
