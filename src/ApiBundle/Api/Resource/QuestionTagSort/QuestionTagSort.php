<?php

namespace ApiBundle\Api\Resource\QuestionTagSort;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\QuestionTag\Service\QuestionTagService;

class QuestionTagSort extends AbstractResource
{
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
