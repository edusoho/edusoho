<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;
use Symfony\Component\HttpFoundation\Request;

class UserFavoriteCourse extends Resource
{
    public function get(Request $request, $userId, $courseId)
    {
        $isFavorite = $this->service('Course:CourseSetService')->isUserFavorite($userId, $courseId);
        return array('isFavorite' => $isFavorite);
    }

    public function search()
    {

    }

}