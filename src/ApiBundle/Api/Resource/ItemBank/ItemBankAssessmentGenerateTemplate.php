<?php

namespace ApiBundle\Api\Resource\ItemBank;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentGenerateRuleService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class ItemBankAssessmentGenerateTemplate extends AbstractResource
{
    public function get(ApiRequest $request, $bankId, $type)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($bankId);
        if (empty($questionBank)) {
            throw QuestionBankException::NOT_FOUND_BANK();
        }
        if (!$this->getQuestionBankService()->canManageBank($questionBank['id'])) {
            throw UserException::PERMISSION_DENIED();
        }
        $assessments = $this->getAssessmentService()->searchAssessments(['bank_id' => $bankId, 'type' => $type, 'parent_id' => 0], ['id' => 'DESC'], 0, 1);
        if (empty($assessments)) {
            return [];
        }

        return $this->getAssessmentGenerateRuleService()->getAssessmentGenerateRuleByAssessmentId($assessments[0]['id']);
    }

    /**
     * @return AssessmentService
     */
    private function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
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
