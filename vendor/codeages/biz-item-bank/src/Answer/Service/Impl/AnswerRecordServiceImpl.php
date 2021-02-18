<?php

namespace Codeages\Biz\ItemBank\Answer\Service\Impl;

use Codeages\Biz\ItemBank\BaseService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerSceneException;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerException;
use Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;

class AnswerRecordServiceImpl extends BaseService implements AnswerRecordService
{
    public function search($conditions, $orderBys, $start, $limit, $columns = array())
    {
        return $this->getAnswerRecordDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function count($conditions)
    {
        return $this->getAnswerRecordDao()->count($conditions);
    }

    public function create($answerRecord = array())
    {
        $answerRecord = $this->getValidator()->validate($answerRecord, [
            'answer_scene_id' => ['required', 'integer'],
            'assessment_id' => ['integer', ['min', 0]],
            'user_id' => ['integer', ['min', 0]],
        ]);

        if ($answerRecord['user_id'] <= 0) {
            $this->createInvalidArgumentException('User id must gt 0');
        }

        $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
        if (empty($answerScene)) {
            throw new AnswerSceneException('AnswerScene not found.', ErrorCode::ANSWER_SCENE_NOTFOUD);
        }

        $assessment = $this->getAssessmentService()->getAssessment($answerRecord['assessment_id']);
        if (empty($assessment)) {
            throw new AssessmentException('Assessment not found.', ErrorCode::ASSESSMENT_NOTFOUND);
        }

        if (AssessmentService::OPEN != $assessment['status']) {
            throw new AssessmentException('Assessment not open.', ErrorCode::ASSESSMENT_NOTOPEN);
        }

        $answerRecord['begin_time'] = time();

        return $this->getAnswerRecordDao()->create($answerRecord);
    }

    public function getLatestAnswerRecordByAnswerSceneIdAndUserId($answerSceneId, $userId)
    {
        $latestAnswerRecord = $this->getAnswerRecordDao()->getLatestAnswerRecordByAnswerSceneIdAndUserId($answerSceneId, $userId);

        return empty($latestAnswerRecord) ? array() : $latestAnswerRecord;
    }

    public function getNextReviewingAnswerRecordByAnswerSceneId($answerSceneId)
    {
        return $this->getAnswerRecordDao()->getNextReviewingAnswerRecordByAnswerSceneId($answerSceneId);
    }

    public function update($id, $answerRecord = array())
    {
        if (empty($this->get($id))) {
            throw new AnswerException('Answer record not found.', ErrorCode::ANSWER_RECORD_NOTFOUND);
        }

        $answerRecord = $this->getValidator()->validate($answerRecord, [
            'status' => [
                ['in', [
                    AnswerService::ANSWER_RECORD_STATUS_DOING,
                    AnswerService::ANSWER_RECORD_STATUS_REVIEWING,
                    AnswerService::ANSWER_RECORD_STATUS_PAUSED,
                    AnswerService::ANSWER_RECORD_STATUS_FINISHED,
                    ],
                ],
            ],
            'used_time' => ['integer'],
            'answer_report_id' => ['integer'],
            'end_time' => ['integer'],
        ]);

        return $this->getAnswerRecordDao()->update($id, $answerRecord);
    }

    public function get($id)
    {
        return $this->getAnswerRecordDao()->get($id);
    }

    public function findByAnswerSceneId($answerSceneId)
    {
        return $this->getAnswerRecordDao()->findByAnswerSceneId($answerSceneId);
    }
    
    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Assessment\Service\AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentService');
    }

    protected function getAnswerRecordDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerRecordDao');
    }
}
