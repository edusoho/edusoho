<?php

namespace ApiBundle\Api\Resource\Assessment;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\Crontab\SystemCrontabInitializer;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\ItemBank\Assessment\Constant\AssessmentType;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class AssessmentRegenerate extends AbstractResource
{
    public function add(ApiRequest $request, $id)
    {
        $assessment = $this->getAssessmentService()->getAssessment($id);
        if (empty($assessment) || AssessmentType::RANDOM != $assessment['type']) {
            throw CommonException::ERROR_PARAMETER();
        }

        if (!in_array($assessment['status'], ['draft', 'failure'])) {
            throw AssessmentException::STATUS_ERROR();
        }
        try {
            $this->biz['db']->beginTransaction();
            $this->getAssessmentService()->updateAssessment($id, ['status' => \Codeages\Biz\ItemBank\Assessment\Constant\AssessmentStatus::GENERATING]);
            $this->getAssessmentService()->deleteAssessmentByParentId($id);
            $this->getSchedulerService()->register([
                'name' => 'RandomAssessmentCreateJob_'.$id,
                'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                'expression' => intval(time() + 10),
                'misfire_policy' => 'executing',
                'class' => 'Biz\Testpaper\Job\RandomAssessmentCreateJob',
                'args' => ['assessmentId' => $id, 'questionBankId' => $assessment['bank_id']],
            ]);
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }

        return ['ok' => true];
    }

    /**
     * @return AssessmentService
     */
    private function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->service('Scheduler:SchedulerService');
    }
}
