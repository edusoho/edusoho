<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\Favorite\Service\FavoriteService;
use Biz\Goods\GoodsEntityFactory;
use Biz\Goods\Service\GoodsService;

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
            'targetType' => 'goods',
            'goodsType' => 'course',
        ];
        $favorites = $this->getFavoriteService()->searchFavorites(
            $conditions,
            ['createdTime' => 'DESC'],
            $offset,
            $limit
        );
        $total = $this->getFavoriteService()->countFavorites($conditions);
        $goods = $this->getGoodsService()->findGoodsByIds(array_column($favorites, 'targetId'));
        $goods = $this->getGoodsEntityFactory()->create('course')->fetchTargets($goods);
        $courseSets = [];
        foreach ($goods as $good) {
            $good['courseSet']['goodsId'] = $good['id'];
            $courseSets[] = $good['courseSet'];
        }
        foreach ($courseSets as &$courseSet) {
            $courseSet['videoMaxLevel'] = $this->getCourseService()->getCourseSetVideoMaxLevel($courseSet['id']);
        }

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
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return FavoriteService
     */
    private function getFavoriteService()
    {
        return $this->service('Favorite:FavoriteService');
    }

    /**
     * @return GoodsEntityFactory
     */
    protected function getGoodsEntityFactory()
    {
        $biz = $this->getBiz();

        return $biz['goods.entity.factory'];
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->service('Goods:GoodsService');
    }
}
