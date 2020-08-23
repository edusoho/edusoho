<?php

namespace ApiBundle\Api\Resource\Favorite;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Favorite\Service\FavoriteService;
use Biz\Goods\Service\GoodsService;

class Favorite extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $favorite = [
            'targetType' => $request->request->get('targetType'),
            'targetId' => $request->request->get('targetId'),
            'userId' => $this->getCurrentUser()->getId(),
        ];
        //商品目前有明确的分类，兼容收藏按照课程和班级分开的功能要求
        if ('goods' === $favorite['targetType']) {
            $goods = $this->getGoodsService()->getGoods($favorite['targetId']);
            $favorite['goodsType'] = empty($goods) ? '' : $goods['type'];
        }

        return $this->getFavoriteService()->createFavorite($favorite);
    }

    public function remove(ApiRequest $request)
    {
        return $this->getFavoriteService()->deleteUserFavorite(
            $this->getCurrentUser()->getId(),
            $request->request->get('targetType'),
            $request->request->get('targetId')
        );
    }

    /**
     * @return FavoriteService
     */
    protected function getFavoriteService()
    {
        return $this->service('Favorite:FavoriteService');
    }

    /**
     * @return GoodsService
     */
    private function getGoodsService()
    {
        return $this->biz->service('Goods:GoodsService');
    }
}
