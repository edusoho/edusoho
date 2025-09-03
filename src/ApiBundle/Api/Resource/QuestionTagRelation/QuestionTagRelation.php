<?php

namespace ApiBundle\Api\Resource\QuestionTagRelation;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\QuestionTag\Service\QuestionTagService;

class QuestionTagRelation extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $this->getQuestionTagService()->tagQuestions($request->request->get('itemIds'), $request->request->get('tagIds'));

        return ['ok' => true];
    }

    /**
     * @return QuestionTagService
     */
    private function getQuestionTagService()
    {
        return $this->getBiz()->service('QuestionTag:QuestionTagService');
    }
}
