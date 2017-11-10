<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class User extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     * @ResponseFilter(class="ApiBundle\Api\Resource\User\UserFilter", mode="simple")
     */
    public function get(ApiRequest $request, $identify)
    {
        $identifyType = $request->query->get('identify_type', 'id');

        $user = null;
        switch ($identifyType) {
            case 'id':
                $user = $this->getUserService()->getUser($identify);
                break;
            case 'email':
                $user = $this->getUserService()->getUserByEmail($identify);
                break;
            case 'mobile':
                $user = $this->getUserService()->getUserByVerifiedMobile($identify);
                break;
            default:
                break;
        }

        return $user;
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}