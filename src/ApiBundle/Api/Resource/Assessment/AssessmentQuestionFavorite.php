<?php

namespace ApiBundle\Api\Resource\Assessment;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Codeages\Biz\ItemBank\Item\Service\QuestionFavoriteService;

class AssessmentQuestionFavorite extends AbstractResource
{
    public function search(ApiRequest $request, $id)
    {
        return $this->getQuestionFavoriteService()->search(
            ['user_id' => $this->getCurrentUser()['id'], 'target_id' => $id],
            [],
            0,
            PHP_INT_MAX
        );
    }

    /**
     * @return QuestionFavoriteService
     */
    protected function getQuestionFavoriteService()
    {
        return $this->service('ItemBank:Item:QuestionFavoriteService');
    }
}
