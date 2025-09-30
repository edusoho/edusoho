<?php

namespace ApiBundle\Api\Resource\QuestionTagRelation;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\QuestionTag\Service\QuestionTagService;

class QuestionTagRelation extends AbstractResource
{
    /**
     * @Access(roles="ROLE_TEACHER", permissions="admin_v2")
     */
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
