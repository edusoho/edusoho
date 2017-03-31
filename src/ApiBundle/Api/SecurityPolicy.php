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
                '/course_sets/\d+/courses',
                '/courses/\d+',
                '/courses/\d+/tasks'
            ),
            'POST' => array(
                '/tokens',
            )
        );
    }
}