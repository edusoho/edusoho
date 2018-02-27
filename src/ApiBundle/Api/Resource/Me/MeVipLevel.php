<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use VipPlugin\Biz\Vip\Service\VipService;

class MeVipLevel extends AbstractResource
{
    public function get(ApiRequest $request, $vipLevelId)
    {
        if (!$this->isPluginInstalled('vip')) {
            throw new NotFoundHttpException('Vip plugin not be installed', null, ErrorCode::RESOURCE_NOT_FOUND);
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
