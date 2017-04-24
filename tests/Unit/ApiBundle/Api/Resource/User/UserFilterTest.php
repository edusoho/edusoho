<?php

namespace Tests\Unit\ApiBundle\Api\Resource\User;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use ApiBundle\ApiTestCase;

class UserFilterTest extends ApiTestCase
{
    public function testFilter()
    {
        $user = $this->getCurrentUser()->toArray();
        $filter = new UserFilter();
        $filter->setMode(Filter::SIMPLE_MODE);

        $filter->filter($user);

        $this->assertEquals(array('id', 'nickname', 'title', 'avatar'), array_keys($user));
    }

    public function testFilterWithAuth()
    {
        $user = $this->getCurrentUser()->toArray();
        $profile = $this->createService('User:UserService')->getUserProfile($user['id']);
        $user = array_merge($user, $profile);
        $filter = new UserFilter();
        $filter->setMode(Filter::AUTHENTICATED_MODE);

        $filter->filter($user);

        $this->assertArrayHasKey('email', $user);
    }
}