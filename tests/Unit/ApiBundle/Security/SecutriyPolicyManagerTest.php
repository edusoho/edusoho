<?php

namespace Tests\Unit\ApiBundle;

use ApiBundle\ApiTestCase;
use ApiBundle\Security\SecurityPolicyManager;
use Symfony\Component\HttpFoundation\Request;

class SecurityPolicyManagerTest extends ApiTestCase
{
    public function testIsInWhiteList()
    {
        $manager = new SecurityPolicyManager($this->getContainer());
        $bool = $manager->isInWhiteList(Request::create('/api/users/11'), 'GET');
        $this->assertTrue($bool);
    }
}