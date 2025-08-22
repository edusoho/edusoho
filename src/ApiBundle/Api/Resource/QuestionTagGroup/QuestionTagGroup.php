<?php

namespace ApiBundle\Api\Resource\QuestionTagGroup;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\QuestionTag\Service\QuestionTagService;

class QuestionTagGroup extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        return $this->getQuestionTagService()->searchTagGroups($request->query->all());
    }

    public function add(ApiRequest $request)
    {
        $this->getQuestionTagService()->createTagGroup($request->request->get('name'));

        return ['ok' => true];
    }

    public function update(ApiRequest $request, $id)
    {
        $this->getQuestionTagService()->updateTagGroup($id, $request->request->all());

        return ['ok' => true];
    }

    public function remove(ApiRequest $request, $id)
    {
        $this->getQuestionTagService()->deleteTagGroup($id);

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
