<?php

namespace ApiBundle\Api\Resource\QuestionTagGroupNameCheck;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\QuestionTag\Service\QuestionTagService;

class QuestionTagGroupNameCheck extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $name = $request->request->get('name');
        if (empty($name)) {
            return ['ok' => true];
        }
        $tagGroup = $this->getQuestionTagService()->getTagGroupByName($name);

        return ['ok' => empty($tagGroup)];
    }

    /**
     * @return QuestionTagService
     */
    private function getQuestionTagService()
    {
        return $this->getBiz()->service('QuestionTag:QuestionTagService');
    }
}
