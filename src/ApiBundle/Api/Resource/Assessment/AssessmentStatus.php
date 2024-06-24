<?php

namespace ApiBundle\Api\Resource\Assessment;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class AssessmentStatus extends AbstractResource
{
    public function add(ApiRequest $request, $id)
    {
        $status = $request->request->get('status');
        $assessment = $this->getAssessmentService()->getAssessment($id);
        $this->validate($status, $assessment);
        try {
            $this->biz['db']->beginTransaction();
            $this->getAssessmentService()->updateAssessment($id, ['status' => $status]);
            if ('random' == $assessment['type']) {
                $this->getAssessmentService()->updateBasicAssessmentByParentId($assessment['id'], ['status' => $status]);
            }
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }

        return ['ok' => true];
    }

    private function validate($status, $assessment)
    {
        if (empty($status) || !in_array($status, ['open', 'closed'])) {
            throw AssessmentException::STATUS_ERROR();
        }
        if (0 != $assessment['parent_id']) {
            throw CommonException::ERROR_PARAMETER();
        }
        if (!in_array($assessment['status'], ['draft', 'open', 'closed'])) {
            throw AssessmentException::STATUS_ERROR();
        }
        if ('open' == $status && !in_array($assessment['status'], ['draft', 'closed'])) {
            throw AssessmentException::STATUS_ERROR();
        }
        if ('closed' == $status && 'open' != $assessment['status']) {
            throw AssessmentException::STATUS_ERROR();
        }
    }

    /**
     * @return AssessmentService
     */
    private function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }
}
