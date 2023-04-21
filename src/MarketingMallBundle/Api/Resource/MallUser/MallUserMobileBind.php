<?php

namespace MarketingMallBundle\Api\Resource\MallUser;

use ApiBundle\Api\Annotation\AuthClass;
use ApiBundle\Api\ApiRequest;
use Biz\User\Service\UserService;
use MarketingMallBundle\Api\Resource\BaseResource;

class MallUserMobileBind extends BaseResource
{
    /**
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     */
    public function add(ApiRequest $request, $id)
    {
        $mobile = $request->request->get('mobile');
        if (!empty($mobile)) {
            $this->getUserService()->changeMobile($id, $mobile);
        }

        return ['success' => true];
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
