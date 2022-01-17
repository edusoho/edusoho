<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Activity\Service\ActivityService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

/**
 * @todo
 */
class HomeworkAssessmentDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        if (!empty($arguments['id'])) {
            return $this->getAssessmentService()->getAssessment($arguments['id']);
        }
        if (!empty($arguments['activityId'])) {
            $activity = $this->getActivityService()->getActivity($arguments['activityId'], true);

            return 'homework' == $activity['mediaType'] ? $this->getAssessmentService()->getAssessment($activity['ext']['assessmentId']) : [];
        }

        return [];
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getServiceKernel()->createService('Activity:ActivityService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->getServiceKernel()->createService('ItemBank:Assessment:AssessmentService');
    }
}
