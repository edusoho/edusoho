<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Codeages\Biz\ItemBank\Item\Service\QuestionFavoriteService;

class MeQuestionFavorite extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = [
            'user_id' => $this->getCurrentUser()['id'],
        ];

        $favoriteQuestions = $this->getQuestionFavoriteService()->search(
            $conditions,
            ['created_time' => 'DESC'],
            $offset,
            $limit
        );

        $questions = $this->getItemService()->findQuestionsByQuestionIds(ArrayToolkit::column($favoriteQuestions, 'question_id'));
        $assessments = $this->getAssessmentService()->findAssessmentsByIds(
            ArrayToolkit::column($favoriteQuestions, 'target_id')
        );

        foreach ($favoriteQuestions as $key => &$favoriteQuestion) {
            if (empty($questions[$favoriteQuestion['question_id']])) {
                unset($favoriteQuestions[$key]);
            } else {
                $favoriteQuestion['question'] = $questions[$favoriteQuestion['question_id']];
                $favoriteQuestion['assessment'] = empty($assessments[$favoriteQuestion['target_id']]) ? [] : $assessments[$favoriteQuestion['target_id']];
            }
        }

        $total = $this->getQuestionFavoriteService()->count($conditions);

        return $this->makePagingObject(array_values($favoriteQuestions), $total, $offset, $limit);
    }

    public function add(ApiRequest $request)
    {
        $questionFavorite = $request->request->all();

        $questionFavorite['user_id'] = $this->getCurrentUser()['id'];

        return $this->getQuestionFavoriteService()->create($questionFavorite);
    }

    public function remove(ApiRequest $request, $id)
    {
        $questionFavorite = $request->request->all();

        $questionFavorite['user_id'] = $this->getCurrentUser()['id'];

        $this->getQuestionFavoriteService()->deleteByQuestionFavorite($questionFavorite);

        return ['result' => true];
    }

    /**
     * @return QuestionFavoriteService
     */
    protected function getQuestionFavoriteService()
    {
        return $this->service('ItemBank:Item:QuestionFavoriteService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }
}
