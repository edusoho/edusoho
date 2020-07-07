<?php

namespace ApiBundle\Api\Resource\Favorite;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Favorite\Service\FavoriteService;

class Favorite extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $favorite = [
            'targetType' => $request->request->get('targetType'),
            'targetId' => $request->request->get('targetId'),
            'userId' => $this->getCurrentUser()->getId(),
        ];

        return $this->getFavoriteService()->createFavorite($favorite);
    }

    public function remove(ApiRequest $request)
    {
        return $this->getFavoriteService()->deleteUserFavorite(
            $this->getCurrentUser()->getId(),
            $request->query->get('targetType'),
            $request->query->get('targetId')
        );
    }

    /**
     * @return FavoriteService
     */
    protected function getFavoriteService()
    {
        return $this->service('Favorite:FavoriteService');
    }
}
