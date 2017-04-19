<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Course\CourseMemberFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class MeCourseLearningProgressFilter extends Filter
{
    public function filter(&$data)
    {
        if ($data['toLearnTasks']) {
            $data['nextTask'] = array_pop($data['toLearnTasks']);
        } else {
            $data['nextTask'] = new \stdClass();
        }
    }
}