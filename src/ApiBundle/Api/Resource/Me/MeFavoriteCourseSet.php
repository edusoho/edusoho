<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\Resource;

class MeFavoriteCourseSet extends Resource
{
    public function get(ApiRequest $request, $courseSetId)
    {
        $isFavorite = $this->service('Course:CourseSetService')->isUserFavorite($this->getCurrentUser()->getId(), $courseSetId);
        return array('isFavorite' => $isFavorite);
    }

    public function add(ApiRequest $request)
    {
        $courseSetId = $request->request->get('courseSetId');
        $success = $this->service('Course:CourseSetService')->favorite($courseSetId);
        return array('success' => $success);
    }

    public function remove(ApiRequest $request, $courseSetId)
    {
        $success = $this->service('Course:CourseSetService')->unfavorite($courseSetId);
        return array('success' => $success);
    }
}