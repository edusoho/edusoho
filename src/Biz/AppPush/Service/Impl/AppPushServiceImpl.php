<?php

namespace Biz\AppPush\Service\Impl;

use Biz\AppPush\Service\AppPushService;
use Biz\BaseService;

class AppPushServiceImpl extends BaseService implements AppPushService
{
    public function bindDevice($params)
    {
        $result = $this->getAppPushService()->inspectTenant();
        if ('ok' != $result['status']) {
            $this->getAppPushService()->enableTenant();
        }

        $this->getAppPushService()->bindDevice($params);
    }

    public function unbindDevice($userId)
    {
        $this->getAppPushService()->unbindDevice($userId);
    }

    /**
     * @return \ESCloud\SDK\Service\AppPushService
     */
    private function getAppPushService()
    {
        return $this->biz['ESCloudSdk.appPush'];
    }
}
