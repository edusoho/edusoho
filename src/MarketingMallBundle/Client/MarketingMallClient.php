<?php

namespace MarketingMallBundle\Client;

use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;

class MarketingMallClient
{
    protected $biz;

    protected $api;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
        $storage = $this->getSettingService()->get('storage', []);
        $this->api = new MarketingMallApi($storage);
    }

    public function __call($method, $arguments)
    {
        return $this->api->$method($arguments);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
