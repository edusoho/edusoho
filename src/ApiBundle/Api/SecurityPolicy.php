<?php

namespace ApiBundle\Api;

use ApiBundle\Security\SecurityPolicyInterface;

class SecurityPolicy implements SecurityPolicyInterface
{
    public function getWhiteList()
    {
        return array(
            'GET'  => array(
                '/users/\d+',
                '/course_sets/\d+',
                '/course_sets/\d+/reviews',
                '/course_sets/\d+/members',
                '/course_sets/\d+/courses',
                '/courses/\d+',
                '/courses/\d+/tasks',
                '/courses/\d+/items',
                '/courses/\d+/members',
                '/courses/\d+/members/\d+',
            ),
            'POST' => array(
                '/tokens',
            )
        );
    }
}