<?php


namespace MarketingMallBundle\Api\Resource\MallInfo;


use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;
use MarketingMallBundle\Biz\Mall\Service\MallService;
use MarketingMallBundle\Client\MarketingMallClient;

class MallInfo extends BaseResource
{
    public function search(ApiRequest $request)
    {
        $result['isShow'] = $this->getMallService()->isShow();
        if ($result['isShow']) {
            $result['isInit'] = $this->getMallService()->isInit();
            if ($result['isInit']) {
                $result['url'] = "https://" . $this->container->getParameter('marketing_mall_url') . "/custom-h5/?tab=home&schoolCode=" . $this->getSetting('marketing_mall.code', null);
                $result['isPageSaved'] = $this->isHomePageSaved();
            }
        }

        return $result;
    }

    protected function isHomePageSaved()
    {
        $client = new MarketingMallClient($this->getBiz());

        return $client->isHomePageSaved();
    }

    protected function getSetting($name, $default = null)
    {
        return $this->biz->service('System:SettingService')->node($name, $default);
    }

    /**
     * @return MallService
     */
    protected function getMallService()
    {
        return $this->biz->service('Mall:MallService');
    }
}