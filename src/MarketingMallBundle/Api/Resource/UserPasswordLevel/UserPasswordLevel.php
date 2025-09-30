<?php

namespace MarketingMallBundle\Api\Resource\UserPasswordLevel;

use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;

class UserPasswordLevel extends BaseResource
{
    public function search(ApiRequest $request)
    {
        return [
            'passwordLevel' => 'high',
        ];
    }
}
