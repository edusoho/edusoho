<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;
use Symfony\Component\HttpFoundation\Request;

class UserFavoriteCourse extends Resource
{
    public function get(Request $request, $userId, $courseId)
    {
        return $this->service('Course:CourseSetService')->isUserFavorite($userId, $courseId);
    }

    public function search()
    {

    }

}