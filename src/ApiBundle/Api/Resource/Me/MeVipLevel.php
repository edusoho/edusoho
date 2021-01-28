<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use VipPlugin\Biz\Vip\Service\VipService;

class MeVipLevel extends AbstractResource
{
    public function get(ApiRequest $request, $vipLevelId)
    {
        if (!$this->isPluginInstalled('vip')) {
            throw CommonException::PLUGIN_IS_NOT_INSTALL();
        }

        $status = $this->getVipService()->checkUserInMemberLevel($this->getCurrentUser()->getId(), $vipLevelId);

        return array('isMember' => $status == 'ok');
    }

    /**
     * @return VipService
     */
    private function getVipService()
    {
        return $this->service('VipPlugin:Vip:VipService');
    }
}
