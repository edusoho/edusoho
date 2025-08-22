<?php

namespace ApiBundle\Api\Resource\QuestionTag;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\QuestionTag\Service\QuestionTagService;

class QuestionTag extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        return $this->getQuestionTagService()->searchTags($request->query->all());
    }

    public function add(ApiRequest $request)
    {
        $this->getQuestionTagService()->createTag($request->request->all());

        return ['ok' => true];
    }

    public function update(ApiRequest $request, $id)
    {
        $this->getQuestionTagService()->updateTag($id, $request->request->all());

        return ['ok' => true];
    }

    public function remove(ApiRequest $request, $id)
    {
        $this->getQuestionTagService()->deleteTag($id);

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
