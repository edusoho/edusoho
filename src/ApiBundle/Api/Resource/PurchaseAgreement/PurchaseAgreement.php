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
        return $this->getSettingService()->get('course_purchase_agreement', ['enabled' => 0, 'title' => '', 'content' => '', 'type' => 'tick']);
    }

    public function add(ApiRequest $request)
    {
        $params = $request->request->all();

        $this->getSettingService()->set('course_purchase_agreement', ArrayToolkit::parts($params, ['enabled', 'title', 'content', 'type']));

        return true;
    }
}
