<?php

namespace Codeages\Biz\ItemBank\Answer\Service\Impl;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Answer\Dao\AnswerRecordDao;
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
            'admission_ticket' => ['required'],
            'exam_mode' => ['integer'],
            'limited_time' => ['integer'],
            'is_items_seq_random' => ['integer'],
            'is_options_seq_random' => ['integer'],
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
            throw AssessmentException::ASSESSMENT_NOTEXIST();
        }

        if (AssessmentService::OPEN != $assessment['status']) {
            throw AssessmentException::ASSESSMENT_NOTOPEN();
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
            'admission_ticket' => [],
            'exam_mode' => ['integer'],
            'limited_time' => ['integer'],
            'id' => ['integer'],
            'exercise_mode' => [['in', [0, 1]]],
            'isTag' => [['in', [0, 1]]],
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

    public function countGroupByAnswerSceneId($conditions)
    {
        return $this->getAnswerRecordDao()->countGroupByAnswerSceneId($conditions);
    }

    public function batchCreateAnswerRecords($answerRecords)
    {
        return $this->getAnswerRecordDao()->batchCreate($answerRecords);
    }

    public function batchUpdateAnswerRecord($ids, $updateColumnsList)
    {
        return $this->getAnswerRecordDao()->batchUpdate($ids, $updateColumnsList);
    }

    public function replaceAssessmentsWithSnapshotAssessments($assessmentSnapshots)
    {
        if (empty($assessmentSnapshots)) {
            return;
        }
        $update = [];
        foreach ($assessmentSnapshots as $assessmentSnapshot) {
            $update[$assessmentSnapshot['origin_assessment_id']] = [
                'assessment_id' => $assessmentSnapshot['snapshot_assessment_id'],
            ];
        }
        $this->getAnswerRecordDao()->batchUpdate(array_keys($update), $update, 'assessment_id');
    }

    public function findByIds($ids)
    {
        $answerRecords = $this->getAnswerRecordDao()->findByIds($ids);

        return ArrayToolkit::index($answerRecords, 'id');
    }

    public function countByAssessmentId($assessmentId)
    {
       return $this->getAnswerRecordDao()->count(['assessment_id' => $assessmentId]);
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

    /**
     * @return AnswerRecordDao
     */
    protected function getAnswerRecordDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerRecordDao');
    }
}
