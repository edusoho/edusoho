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
                '/courses/\d+',
                '/course_sets/\d+/courses',
                '/courses/\d+/tasks',
                '/courses_sets/\d+/reviews',
                '/courses/\d+/reviews',
            ),
            'POST' => array(
                '/tokens',
            )
        );
    }
}