<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\Impl\UserServiceImpl;

class MeBinding extends AbstractResource
{
    public function remove(ApiRequest $request, $type)
    {
        $user = $this->getCurrentUser();
        $this->getUserService()->unBindUserByTypeAndToId($type, $user->id);

        return array('success' => true);
    }

    /**
     * @return UserServiceImpl
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
