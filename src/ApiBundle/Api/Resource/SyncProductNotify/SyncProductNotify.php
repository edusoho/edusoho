<?php

namespace ApiBundle\Api\Resource\SyncProductNotify;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\S2B2C\Service\SupplierProductNotifyService;

class SyncProductNotify extends AbstractResource
{
    /**
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     * @ApiConf(isRequiredAuth=true)
     */
    public function add(ApiRequest $request)
    {
        $params = $request->request->get('body');
        try {
            $result = $this->getSupplierProductNotifyService()->syncSupplierProductEvent(new NotifyEvent($params));
            if ($result) {
                return ['status' => true];
            }
        } catch (\Exception $e) {
            $this->getBiz()->offsetGet('s2b2c.merchant.logger')->error('[SyncProductNotify]接口错误 '.$e->getMessage());
        }

        return ['status' => false];
    }

    /**
     * @return SupplierProductNotifyService
     */
    private function getSupplierProductNotifyService()
    {
        return $this->service('S2B2C:SupplierProductNotifyService');
    }
}
