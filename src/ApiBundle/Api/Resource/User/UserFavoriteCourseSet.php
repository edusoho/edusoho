<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\Resource;

class UserFavoriteCourseSet extends Resource
{
    public function get(ApiRequest $request, $userId, $courseSetId)
    {
        $isFavorite = $this->service('Course:CourseSetService')->isUserFavorite($userId, $courseSetId);
        return array('isFavorite' => $isFavorite);
    }

    public function search()
    {

    }

}