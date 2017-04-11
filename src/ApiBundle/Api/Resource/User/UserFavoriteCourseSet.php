<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;
use Symfony\Component\HttpFoundation\Request;

class UserFavoriteCourseSet extends Resource
{
    public function get(Request $request, $userId, $courseSetId)
    {
        $isFavorite = $this->service('Course:CourseSetService')->isUserFavorite($userId, $courseSetId);
        return array('isFavorite' => $isFavorite);
    }

    public function search()
    {

    }

}