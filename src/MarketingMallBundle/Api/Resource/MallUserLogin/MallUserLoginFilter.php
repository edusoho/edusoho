<?php

namespace MarketingMallBundle\Api\Resource\MallUserLogin;

use ApiBundle\Api\Resource\Filter;

class MallUserLoginFilter extends Filter
{
    protected $simpleFields = ['id', 'nickname', 'email', 'verifiedMobile', 'password', 'smallAvatar', 'locked'];
}
