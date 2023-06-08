<?php

namespace ApiBundle\Api\Resource\PurchaseAgreement;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\User\UserException;

class PurchaseAgreement extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }

        return $this->getSettingService()->get('course_purchase_agreement', ['enabled' => 0, 'title' => '', 'content' => '', 'type' => 'tick']);
    }

    public function add(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }

        $params = $request->request->all();

        $this->getSettingService()->set('course_purchase_agreement', ArrayToolkit::parts($params, ['enabled', 'title', 'content', 'type']));

        return true;
    }
}
