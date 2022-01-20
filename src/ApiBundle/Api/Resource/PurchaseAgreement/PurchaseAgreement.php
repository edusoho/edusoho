<?php

namespace ApiBundle\Api\Resource\PurchaseAgreement;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;

class PurchaseAgreement extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        return $this->getSettingService()->get('course_purchase_agreement', []);
    }

    public function add(ApiRequest $request)
    {
        $params = $request->request->all();

        $this->getSettingService()->set('course_purchase_agreement', ArrayToolkit::parts($params, ['enabled', 'title', 'content', 'type']));

        return true;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
