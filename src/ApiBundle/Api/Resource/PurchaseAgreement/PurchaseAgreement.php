<?php

namespace ApiBundle\Api\Resource\PurchaseAgreement;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;

class PurchaseAgreement extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        return $this->getSettingService()->get('course_purchase_agreement', ['enabled' => 0, 'title' => '', 'content' => '', 'type' => 'tick']);
    }

    /**
     * @return array
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        $params['content'] = $this->purifyHtml($params['content']);
        $this->getSettingService()->set('course_purchase_agreement', ArrayToolkit::parts($params, ['enabled', 'title', 'content', 'type']));

        return true;
    }

    protected function purifyHtml($html, $trusted = false)
    {
        $htmlHelper = $this->biz['html_helper'];

        return $htmlHelper->purify($html, $trusted);
    }
}
