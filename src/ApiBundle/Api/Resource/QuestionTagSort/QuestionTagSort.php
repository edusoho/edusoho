<?php

namespace ApiBundle\Api\Resource\QuestionTagSort;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\QuestionTag\Service\QuestionTagService;

class QuestionTagSort extends AbstractResource
{
    /**
     * @Access(permissions="admin_v2_question_tag_manage")
     */
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        if (!ArrayToolkit::requireds($params, ['groupId', 'ids'], true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $this->getQuestionTagService()->sortTags($params['groupId'], $params['ids']);

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
