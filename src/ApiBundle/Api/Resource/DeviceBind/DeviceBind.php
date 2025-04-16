<?php

namespace ApiBundle\Api\Resource\DeviceBind;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\AppPush\Service\AppPushService;

class DeviceBind extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        $this->getAppPushService()->bindDevice([
            'userId' => $this->getCurrentUser()->getId(),
            'deviceToken' => $params['deviceToken'],
            'platform' => $params['platform'],
        ]);

        return ['ok' => true];
    }

    public function remove(ApiRequest $request)
    {
        $this->getAppPushService()->unbindDevice($this->getCurrentUser()->getId());

        return ['ok' => true];
    }

    /**
     * @return AppPushService
     */
    private function getAppPushService()
    {
        return $this->biz->service('AppPush:AppPushService');
    }
}
