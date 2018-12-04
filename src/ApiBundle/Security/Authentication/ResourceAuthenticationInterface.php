<?php

namespace ApiBundle\Security\Authentication;

use ApiBundle\Api\Resource\ResourceProxy;

interface ResourceAuthenticationInterface
{
    public function authenticate(ResourceProxy $proxy, $method);
}
