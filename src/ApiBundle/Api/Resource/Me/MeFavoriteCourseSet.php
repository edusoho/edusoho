<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseSetService;

class MeFavoriteCourseSet extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\CourseSet\CourseSetFilter", mode="simple")
     */
    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $favorites = $this->getCourseSetService()->searchUserFavorites($this->getCurrentUser()->getId(), $offset, $limit);
        $total = $this->getCourseSetService()->countUserFavorites($this->getCurrentUser()->getId());
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(array_column($favorites, 'courseSetId'));

        return $this->makePagingObject(array_values($courseSets), $total, $offset, $limit);
    }

    public function get(ApiRequest $request, $courseSetId)
    {
        $isFavorite = $this->getCourseSetService()->isUserFavorite($this->getCurrentUser()->getId(), $courseSetId);

        return array('isFavorite' => $isFavorite);
    }

    public function add(ApiRequest $request)
    {
        $courseSetId = $request->request->get('courseSetId');
        $success = $this->getCourseSetService()->favorite($courseSetId);

        return array('success' => $success);
    }

    public function remove(ApiRequest $request, $courseSetId)
    {
        $success = $this->getCourseSetService()->unfavorite($courseSetId);

        return array('success' => $success);
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }
}
