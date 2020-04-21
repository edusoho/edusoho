<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Codeages\Biz\ItemBank\Item\Service\QuestionFavoriteService;

class MeQuestionFavorite extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $questionFavorite = $request->request->all();

        $questionFavorite['user_id'] = $this->getCurrentUser()['id'];

        return $this->getQuestionFavoriteService()->create($questionFavorite);
    }

    public function remove(ApiRequest $request, $id)
    {
        $this->getQuestionFavoriteService()->delete($id);

        return array('result' => true);
    }

    /**
     * @return QuestionFavoriteService
     */
    protected function getQuestionFavoriteService()
    {
        return $this->service('ItemBank:Item:QuestionFavoriteService');
    }
}
