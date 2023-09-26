<?php

namespace Biz\Activity\Job;

use Biz\Activity\Dao\HomeworkActivityDao;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class HomeworkActivitySetAssessmentBankIdJob extends AbstractJob
{
    public function execute()
    {
        $homeworkActivities = $this->getHomeworkActivityDao()->search(['assessmentBankId' => 0], [], 0, 1000, ['id', 'assessmentId']);
        if (empty($homeworkActivities)) {
            return;
        }
        $assessments = $this->getAssessmentService()->searchAssessments(
            ['ids' => array_column($homeworkActivities, 'assessmentId'), 'displayable' => 0],
            [],
            0,
            count($homeworkActivities),
            ['id', 'bank_id']
        );
        $assessments = array_column($assessments, null, 'id');
        $updateHomeworkActivities = [];
        foreach ($homeworkActivities as $homeworkActivity) {
            if (!empty($assessments[$homeworkActivity['assessmentId']])) {
                $updateHomeworkActivities[$homeworkActivity['id']] = [
                    'assessmentBankId' => $assessments[$homeworkActivity['assessmentId']]['bank_id'],
                ];
            }
        }
        if ($updateHomeworkActivities) {
            $this->getHomeworkActivityDao()->batchUpdate(array_keys($updateHomeworkActivities), $updateHomeworkActivities);
        }
        if (1000 === count($homeworkActivities)) {
            $this->getSchedulerService()->register([
                'name' => 'HomeworkActivitySetAssessmentBankIdJob',
                'expression' => time(),
                'class' => 'Biz\Activity\Job\HomeworkActivitySetAssessmentBankIdJob',
                'misfire_threshold' => 60 * 60,
                'misfire_policy' => 'executing',
            ]);
        }
    }

    /**
     * @return AssessmentService
     */
    private function getAssessmentService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }

    /**
     * @return HomeworkActivityDao
     */
    private function getHomeworkActivityDao()
    {
        return $this->biz->dao('Activity:HomeworkActivityDao');
    }
}
