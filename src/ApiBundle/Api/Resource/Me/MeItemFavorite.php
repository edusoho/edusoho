<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Codeages\Biz\ItemBank\Item\Service\ItemFavoriteService;

class MeItemFavorite extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $itemFavorite = $request->request->all();

        $itemFavorite['user_id'] = $this->getCurrentUser()['id'];
        
        return $this->getItemFavoriteService()->create($itemFavorite);
    }

    public function remove(ApiRequest $request, $id)
    {
        $this->getItemFavoriteService()->delete($id);
        
        return array('result' => true);
    }

    /**
     * @return ItemFavoriteService
     */
    protected function getItemFavoriteService()
    {
        return $this->service('ItemBank:Item:ItemFavoriteService');
    }
}
