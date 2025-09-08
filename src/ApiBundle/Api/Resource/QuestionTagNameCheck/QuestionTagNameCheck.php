<?php

namespace ApiBundle\Api\Resource\QuestionTagNameCheck;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\QuestionTag\Service\QuestionTagService;

class QuestionTagNameCheck extends AbstractResource
{
    /**
     * @Access(permissions="admin_v2_question_tag_manage")
     */
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        if (!ArrayToolkit::requireds($params, ['groupId', 'name'], true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $tag = $this->getQuestionTagService()->getTagByGroupIdAndName($params['groupId'], $params['name']);

        return ['ok' => empty($tag)];
    }

    /**
     * @return QuestionTagService
     */
    private function getQuestionTagService()
    {
        return $this->getBiz()->service('QuestionTag:QuestionTagService');
    }
}
