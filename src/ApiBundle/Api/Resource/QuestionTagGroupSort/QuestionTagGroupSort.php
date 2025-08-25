<?php

namespace ApiBundle\Api\Resource\QuestionTagGroupSort;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\QuestionTag\Service\QuestionTagService;

class QuestionTagGroupSort extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $this->getQuestionTagService()->sortTagGroups($request->request->get('ids'));

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
