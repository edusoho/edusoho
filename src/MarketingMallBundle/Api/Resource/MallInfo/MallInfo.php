<?php


namespace MarketingMallBundle\Api\Resource\MallInfo;


use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;
use MarketingMallBundle\Client\MarketingMallClient;

class MallInfo extends BaseResource
{
    public function search(ApiRequest $request)
    {
        $result['isShow'] = $this->getSetting('cloud_status.accessCloud', false) && !$this->getSetting('developer.without_network', $default = false);
        if($result['isShow']) {
            $result['isInit'] = !empty($this->getSetting('marketing_mall.access_key', false));
            if($result['isInit']) {
                $result['url'] = $request->getHttpRequest()->getScheme().$this->container->getParameter('marketing_mall_url')."/custom-h5/?tab=home&schoolCode=".$this->getSetting('marketing_mall.code', null);
                $result['isPageSaved'] = $this->isHomePageSaved();
            }
        }
        return $result;
    }

    protected function isHomePageSaved() {
        $client = new MarketingMallClient($this->getBiz());
        return $client->isHomePageSaved();
    }

    protected function getSetting($name, $default = null)
    {
        return $this->biz->service('System:SettingService')->node($name, $default);
    }
}