<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Favorite\Service\FavoriteService;
use Biz\Goods\GoodsEntityFactory;

class MeFavorite extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $conditions = [
            'userId' => $this->getCurrentUser()->getId(),
        ];

        $types = $request->query->get('types', []);

        if (empty(!$types)) {
            $conditions['targetTypes'] = $types;
        }

        $total = $this->getFavoriteService()->countFavorites($conditions);

        $favorites = $this->getFavoriteService()->searchFavorites(
            $conditions,
            ['createdTime' => 'DESC'],
            $offset,
            $limit
        );

        return $this->makePagingObject(array_values($favorites), $total, $offset, $limit);
    }

    /**
     * @return FavoriteService
     */
    protected function getFavoriteService()
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
}
