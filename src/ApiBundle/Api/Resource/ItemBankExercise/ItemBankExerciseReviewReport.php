<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;

class ItemBankExerciseReviewReport extends AbstractResource
{
    public function add(ApiRequest $request, $exerciseId)
    {
        $user = $this->getCurrentUser();

        $reviewReport = $request->request->all();
        $answerReport = $this->getAnswerReportService()->get($reviewReport['report_id']);
        if (empty($answerReport) || $this->getCurrentUser()['id'] != $answerReport['user_id']) {
            throw CommonException::ERROR_PARAMETER();
        }

        $reviewReport = $request->request->all();

        return $this->getAnswerService()->review($reviewReport);
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerService;
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
     */
    protected function getAnswerReportService()
    {
        return $this->service('ItemBank:Answer:AnswerReportService');
    }
}
