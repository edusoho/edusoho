<?php

namespace ApiBundle\Api\Resource\Assessment;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentGenerateRuleService;

class AssessmentGenerateTemplate extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        if (empty($conditions['itemBankId'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($conditions['itemBankId']);
        if (empty($questionBank['itemBank'])) {
            throw QuestionBankException::NOT_FOUND_BANK();
        }
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        return $this->getAssessmentGenerateRuleService()->search($conditions, ['created_time' => 'desc'], $offset, $limit);
    }

    /**
     * @return AssessmentGenerateRuleService
     */
    private function getAssessmentGenerateRuleService()
    {
        return $this->service('ItemBank:Assessment:AssessmentGenerateRuleService');
    }

    /**
     * @return QuestionBankService
     */
    private function getQuestionBankService()
    {
        return $this->service('QuestionBank:QuestionBankService');
    }
}
