<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseSetService;
use Biz\Favorite\Service\FavoriteService;

class MeFavoriteCourseSet extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\CourseSet\CourseSetFilter", mode="simple")
     */
    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = [
            'userId' => $this->getCurrentUser()->getId(),
            'targetType' => 'course',
        ];
        $favorites = $this->getFavoriteService()->searchFavorites(
            $conditions,
            ['createdTime' => 'DESC'],
            $offset,
            $limit
        );
        $total = $this->getFavoriteService()->countFavorites($conditions);
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(array_column($favorites, 'targetId'));

        return $this->makePagingObject(array_values($courseSets), $total, $offset, $limit);
    }

    public function get(ApiRequest $request, $courseSetId)
    {
        return [
            'isFavorite' => !empty($this->getFavoriteService()->getUserFavorite($this->getCurrentUser()->getId(), 'course', $courseSetId)),
        ];
    }

    public function add(ApiRequest $request)
    {
        $result = $this->getFavoriteService()->createFavorite([
            'targetType' => 'course',
            'targetId' => $request->request->get('courseSetId'),
            'userId' => $this->getCurrentUser()->getId(),
        ]);

        return ['success' => !empty($result)];
    }

    public function remove(ApiRequest $request, $courseSetId)
    {
        $success = $this->getFavoriteService()->deleteUserFavorite($this->getCurrentUser()->getId(), 'course', $courseSetId);

        return ['success' => $success];
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }

    /**
     * @return FavoriteService
     */
    private function getFavoriteService()
    {
        return $this->service('Favorite:FavoriteService');
    }
}
