<?php

namespace ApiBundle\Api\Resource\QuestionTag;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\QuestionTag\Service\QuestionTagService;

class QuestionTag extends AbstractResource
{
    /**
     * @Access(permissions="admin_v2_question_tag_manage")
     */
    public function search(ApiRequest $request)
    {
        return $this->getQuestionTagService()->searchTags($request->query->all());
    }

    /**
     * @Access(permissions="admin_v2_question_tag_manage")
     */
    public function add(ApiRequest $request)
    {
        $this->getQuestionTagService()->createTag($request->request->all());

        return ['ok' => true];
    }

    /**
     * @Access(permissions="admin_v2_question_tag_manage")
     */
    public function update(ApiRequest $request, $id)
    {
        $this->getQuestionTagService()->updateTag($id, $request->request->all());

        return ['ok' => true];
    }

    /**
     * @Access(permissions="admin_v2_question_tag_manage")
     */
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
