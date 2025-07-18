<?php

namespace Codeages\Biz\ItemBank\Answer\Service\Impl;

use ApiBundle\Api\Util\AssetHelper;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Common\CommonException;
use Biz\Question\Service\QuestionService;
use Biz\WrongBook\Dao\WrongQuestionDao;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Answer\Constant\AnswerRecordStatus;
use Codeages\Biz\ItemBank\Answer\Constant\ExerciseMode;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerException;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerReportException;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerSceneException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRandomSeqService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReviewedQuestionService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionTagService;
use Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionService;
use Codeages\Biz\ItemBank\BaseService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;
use Codeages\Biz\ItemBank\Item\Service\AttachmentService;
use Codeages\Biz\ItemBank\Item\Type\Question;
use Ramsey\Uuid\Uuid;

class AnswerServiceImpl extends BaseService implements AnswerService
{
    const EXAM_MODE_SIMULATION = 0;

    public function startAnswer($answerSceneId, $assessmentId, $userId)
    {
        if (!$this->getAnswerSceneService()->canStart($answerSceneId, $userId)) {
            throw new AnswerSceneException('AnswerScene did not start.', ErrorCode::ANSWER_SCENE_NOTSTART);
        }
        $this->modifyAssessmentIfItemDeleted($assessmentId);
        if ($this->getAssessmentService()->isEmptyAssessment($assessmentId)) {
            throw new AnswerException('试卷全部题目已被删除，请联系教师或管理员', ErrorCode::ASSESSMENT_EMPTY);
        }
        $answerScene = $this->getAnswerSceneService()->get($answerSceneId);

        $answerRecord = $this->getAnswerRecordService()->create([
            'answer_scene_id' => $answerSceneId,
            'assessment_id' => $assessmentId,
            'user_id' => $userId,
            'admission_ticket' => $this->generateAdmissionTicket(),
            'exam_mode' => $answerScene['exam_mode'],
            'limited_time' => $answerScene['limited_time'],
            'is_items_seq_random' => $answerScene['is_items_seq_random'],
            'is_options_seq_random' => $answerScene['is_options_seq_random'],
        ]);
        $this->getAnswerRandomSeqService()->createAnswerRandomSeqRecordIfNecessary($answerRecord['id']);

        $this->dispatch('answer.started', $answerRecord);

        $this->registerAutoSubmitJob($answerRecord);

        return $answerRecord;
    }

    protected function generateAdmissionTicket()
    {
        return Uuid::uuid1()->getHex();
    }

    public function submitAnswer(array $assessmentResponse)
    {
        // todo 背景信息
        $assessmentResponse = $this->appendNoAnswerQuestion($assessmentResponse);
        $assessmentResponse = $this->convertAssessmentResponse($assessmentResponse);
        $assessmentResponse = $this->validateAssessmentResponse($assessmentResponse);
        $assessmentResponse = $this->getAnswerRandomSeqService()->restoreOptionsToOriginalSeqIfNecessary($assessmentResponse);
        $assessmentReport = $this->getAssessmentService()->review(
            $assessmentResponse['assessment_id'],
            $assessmentResponse['section_responses']
        );
        $assessmentReport['answer_record_id'] = $assessmentResponse['answer_record_id'];
        $answerQuestionReports = $this->getAnswerQuestionReportsByAssessmentReport($assessmentReport);

        $answerRecord = $this->getAnswerRecordService()->get($assessmentReport['answer_record_id']);

        try {
            $this->beginTransaction();

            $this->saveAnswerQuestionReport($answerQuestionReports, $answerRecord['id']);
            $this->saveAnswerQuestionTag($assessmentResponse, $answerRecord);
            $attachments = $this->getAttachmentsFromAssessmentResponse($assessmentResponse);
            $this->updateAttachmentsTarget($answerRecord['id'], $attachments);
            $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
            $isFinished = $this->isFinished($answerQuestionReports, $answerScene);
            list($answerRecord) = $this->generateAnswerReport($answerQuestionReports, $answerRecord, $assessmentResponse['used_time'], $isFinished);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        $this->dispatch('answer.submitted', $answerRecord);

        return $answerRecord;
    }

    public function buildAutoSubmitAssessmentResponse($answerRecordId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
        $assessmentResponse = ['answer_record_id' => $answerRecordId, 'assessment_id' => $answerRecord['assessment_id']];
        $answerQuestionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecordId);
        if (empty($answerQuestionReports)) {
            $this->batchCreateAnswerQuestionReports($answerRecord['assessment_id'], [$answerRecord]);
            $answerQuestionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecordId);
        }
        $answerQuestionReports = $this->getAnswerRandomSeqService()->shuffleQuestionReportsAndConvertOptionsIfNecessary($answerQuestionReports, $answerRecordId);
        $sectionResponses = ArrayToolkit::group($answerQuestionReports, 'section_id');
        foreach ($sectionResponses as $sectionId => &$sectionResponse) {
            $itemResponses = ArrayToolkit::group($sectionResponse, 'item_id');
            foreach ($itemResponses as $itemId => &$itemResponse) {
                foreach ($itemResponse as &$questionResponses) {
                    $questionResponses = ArrayToolkit::parts($questionResponses, ['question_id', 'response']);
                }
                $itemResponse = ['question_responses' => $itemResponse];
                $itemResponse['item_id'] = $itemId;
            }
            $itemResponses = array_values($itemResponses);
            $sectionResponse = ['item_responses' => $itemResponses];
            $sectionResponse['section_id'] = $sectionId;
        }
        $assessmentResponse['section_responses'] = array_values($sectionResponses);
        // 自动交卷时认为用时即考试限制时间
        $assessmentResponse['used_time'] = $answerScene['limited_time'] * 60;

        return $assessmentResponse;
    }

    public function batchAutoSubmit($answerSceneId, $assessmentId, $userIds)
    {
        if (empty($userIds)) {
            throw new AnswerException('没有要自动交卷的用户', ErrorCode::NO_USER_AUTO_SUMBMIT_ANSWER);
        }

        $answerScene = $this->getAnswerSceneService()->get($answerSceneId);
        if (empty($answerScene)) {
            throw new AnswerSceneException('AnswerScene not found.', ErrorCode::ANSWER_SCENE_NOTFOUD);
        }
        if (empty($answerScene['end_time']) || $answerScene['end_time'] >= time()) {
            throw new AnswerSceneException('AnswerScene endTime within expory date.', ErrorCode::ANSWER_ENDTIME_WITHIN_EXPIRY_DATE);
        }

        $assessment = $this->getAssessmentService()->getAssessment($assessmentId);
        if (empty($assessment)) {
            throw AssessmentException::ASSESSMENT_NOTEXIST();
        }

        $answerRecords = $this->batchCreateAnswerRecords($answerScene, $assessmentId, $userIds);
        $answerReports = $this->batchCreateAnswerReports($assessment, $answerRecords);

        $updateAnswerRecords = [];
        $answerReports = array_column($answerReports, null, 'answer_record_id');
        foreach ($answerRecords as $answerRecord) {
            $updateAnswerRecords[] = [
                'answer_report_id' => $answerReports[$answerRecord['id']]['id'],
            ];
        }
        $this->getAnswerRecordService()->batchUpdateAnswerRecord(array_column($answerRecords, 'id'), $updateAnswerRecords);

        $this->batchCreateAnswerQuestionReports($assessmentId, $answerRecords);
    }

    protected function batchCreateAnswerRecords($answerScene, $assessmentId, $userIds)
    {
        $newAnswerRecords = [];
        $newAnswerRecord = [
            'answer_scene_id' => $answerScene['id'],
            'assessment_id' => $assessmentId,
            'exam_mode' => $answerScene['exam_mode'],
            'limited_time' => $answerScene['limited_time'],
            'status' => 'finished',
            'begin_time' => $answerScene['end_time'],
            'end_time' => $answerScene['end_time'],
            'created_time' => $answerScene['end_time'],
            'updated_time' => $answerScene['end_time'],
        ];
        foreach ($userIds as $userId) {
            $newAnswerRecord['user_id'] = $userId;
            $newAnswerRecords[] = $newAnswerRecord;
        }

        $this->getAnswerRecordService()->batchCreateAnswerRecords($newAnswerRecords);

        return $this->getAnswerRecordService()->search(['answer_scene_id' => $answerScene['id'], 'user_ids' => $userIds], [], 0, count($userIds), ['id', 'user_id', 'answer_scene_id']);
    }

    protected function batchCreateAnswerReports($assessment, $answerRecords)
    {
        $newAnswerReports = [];
        $newAnswerReport = [
            'assessment_id' => $assessment['id'],
            'total_score' => $assessment['total_score'],
            'score' => 0,
            'subjective_score' => 0,
            'objective_score' => 0,
            'right_rate' => 0,
            'right_question_count' => 0,
            'review_time' => time(),
        ];

        foreach ($answerRecords as $answerRecord) {
            $newAnswerReport['answer_record_id'] = $answerRecord['id'];
            $newAnswerReport['user_id'] = $answerRecord['user_id'];
            $newAnswerReport['answer_scene_id'] = $answerRecord['answer_scene_id'];
            $newAnswerReports[] = $newAnswerReport;
        }

        $this->getAnswerReportService()->batchCreateAnswerReports($newAnswerReports);
        $answerRecordIds = array_column($answerRecords, 'id');

        return $this->getAnswerReportService()->search(['answer_record_ids' => $answerRecordIds], [], 0, count($answerRecordIds), ['id', 'answer_record_id']);
    }

    protected function batchCreateAnswerQuestionReports($assessmentId, $answerRecords)
    {
        if ($this->getAssessmentService()->isEmptyAssessment($assessmentId)) {
            return;
        }
        $sections = $this->getAssessmentSectionService()->findSectionDetailByAssessmentId($assessmentId);

        $answerQuestionReports = [];
        $newAnswerQuestionReport = [
            'assessment_id' => $assessmentId,
            'status' => AnswerQuestionReportService::STATUS_NOANSWER,
        ];

        foreach ($answerRecords as $answerRecord) {
            $newAnswerQuestionReport['answer_record_id'] = $answerRecord['id'];
            foreach ($sections as $section) {
                $newAnswerQuestionReport['section_id'] = $section['id'];
                foreach ($section['items'] as $item) {
                    $newAnswerQuestionReport['item_id'] = $item['id'];
                    foreach ($item['questions'] as $question) {
                        $newAnswerQuestionReport['question_id'] = $question['id'];
                        $newAnswerQuestionReport['identify'] = $answerRecord['id'] . '_' . $question['id'];
                        $answerQuestionReports[] = $newAnswerQuestionReport;
                    }
                }
            }
        }

        $this->getAnswerQuestionReportService()->batchCreate($answerQuestionReports);
    }

    public function submitSingleAnswer($answerRecordId, $params)
    {
        $reviewedQuestion = $this->getAnswerReviewedQuestionService()->getByAnswerRecordIdAndQuestionId($answerRecordId, $params['question_id']);
        if ($reviewedQuestion) {
            throw new AnswerException('该题已提交，不能再次提交', ErrorCode::ANSWER_SUMBMITTED);
        }

        $answerQuestionReport = $this->reviewSingleAnswer($answerRecordId, $params);
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);

        try {
            $this->beginTransaction();

            $questionReport = $this->getAnswerQuestionReportService()->getByAnswerRecordIdAndQuestionId($answerRecord['id'], $params['question_id']);
            if ($questionReport) {
                $answerQuestionReport = $this->getAnswerQuestionReportService()->updateAnswerQuestionReport($questionReport['id'], $answerQuestionReport);
            } else {
                $answerQuestionReport = $this->getAnswerQuestionReportService()->createAnswerQuestionReport($answerQuestionReport);
            }
            $attachments = $this->getSingleAnswerAttachments($params);
            $this->updateAttachmentsTarget($answerRecord['id'], $attachments);

            $answerReviewedQuestion = $this->createAnswerReviewedQuestion($answerRecord['id'], $answerQuestionReport['question_id']);
            if (!$this->needManualMarking($answerQuestionReport['status'], $answerRecord['answer_scene_id'])) {
                $this->getAnswerReviewedQuestionService()->updateAnswerReviewedQuestion($answerReviewedQuestion['id'], ['is_reviewed' => 1]);
                $answerQuestionReport['isReviewed'] = true;
            }

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $answerQuestionReport;
    }

    private function needManualMarking($status, $answerSceneId)
    {
        if (AnswerQuestionReportService::STATUS_REVIEWING != $status) {
            return false;
        }
        $answerScene = $this->getAnswerSceneService()->get($answerSceneId);

        return $answerScene['manual_marking'];
    }

    private function createAnswerReviewedQuestion($answerRecordId, $questionId)
    {
        $answerReviewedQuestion = [
            'answer_record_id' => $answerRecordId,
            'question_id' => $questionId,
        ];

        return $this->getAnswerReviewedQuestionService()->createAnswerReviewedQuestion($answerReviewedQuestion);
    }

    public function finishAllSingleAnswer($answerRecord, $type)
    {
        if (!in_array($type, ['submit', 'review', 'finish'])) {
            throw CommonException::ERROR_PARAMETER();
        }

        $answerQuestionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecord['id']);
        list($answerRecord, $answerReport) = $this->generateAnswerReport($answerQuestionReports, $answerRecord);

        if ($type == 'submit') {
            $this->dispatch('answer.submitted', $answerRecord);
        } elseif ($type == 'review') {
            $this->dispatch('answer.finished', $answerReport);
        }
    }

    protected function getSingleAnswerAttachments($params)
    {
        $attachments = [];
        if (empty($params['attachments'])) {
            return $attachments;
        }
        foreach ($params['attachments'] as $attachment) {
            $attachments[] = [
                'id' => $attachment['id'],
                'module' => $attachment['module'],
                'question_id' => $params['question_id'],
            ];
        }

        return $attachments;
    }

    protected function reviewSingleAnswer($answerRecordId, $params)
    {
        $questionReport = $this->getQuestionProcessor()->review($params['question_id'], empty($params['response']) ? [] : $params['response']);
        if ('none' == $questionReport['result']) {
            $questionReport['result'] = AnswerQuestionReportService::STATUS_REVIEWING;
        }

        $answerQuestionReport = [
            'identify' => $answerRecordId . '_' . $questionReport['question_id'],
            'total_score' => empty($questionReport['total_score']) ? 0.0 : $questionReport['total_score'],
            'answer_record_id' => $answerRecordId,
            'assessment_id' => $params['assessment_id'],
            'section_id' => $params['section_id'],
            'item_id' => $params['item_id'],
            'question_id' => $questionReport['question_id'],
            'score' => empty($questionReport['score']) ? 0.0 : $questionReport['score'],
            'status' => empty($questionReport['result']) ? '' : $questionReport['result'],
            'response' => empty($questionReport['response']) ? [] : $questionReport['response'],
        ];

        return $answerQuestionReport;
    }

    protected function generateAnswerReport($answerQuestionReports, $answerRecord, $usedTime = 0, $isFinished = true)
    {
        $subjectiveScore = $this->sumSubjectiveScore($answerQuestionReports);
        $score = $this->sumScore($answerQuestionReports);

        $answerReport = $this->getAnswerReportService()->create([
            'user_id' => $answerRecord['user_id'],
            'assessment_id' => $answerRecord['assessment_id'],
            'answer_record_id' => $answerRecord['id'],
            'total_score' => $this->sumTotalScore($answerQuestionReports),
            'score' => $score,
            'subjective_score' => $subjectiveScore,
            'objective_score' => $score - $subjectiveScore,
            'right_rate' => $this->sumRightRate($answerQuestionReports),
            'right_question_count' => $this->getRightQuestionCount($answerQuestionReports),
            'review_time' => $isFinished ? time() : 0,
        ]);

        $answerRecord = $this->getAnswerRecordService()->update(
            $answerRecord['id'],
            [
                'answer_report_id' => $answerReport['id'],
                'status' => $isFinished ? AnswerRecordStatus::FINISHED : AnswerRecordStatus::REVIEWING,
                'end_time' => time(),
                'used_time' => $usedTime ?: time() - $answerRecord['created_time'],
            ]
        );

        if ($isFinished) {
            $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
            $this->getAnswerSceneService()->update(
                $answerScene['id'],
                ['name' => $answerScene['name'], 'last_review_time' => time()]
            );
        }

        return [$answerRecord, $answerReport];
    }

    protected function sumTotalScore(array $answerQuestionReports)
    {
        return array_sum(ArrayToolkit::column($answerQuestionReports, 'total_score'));
    }

    protected function getRightQuestionCount(array $answerQuestionReports)
    {
        $answerQuestionReports = ArrayToolkit::group($answerQuestionReports, 'status');

        return empty($answerQuestionReports[AnswerQuestionReportService::STATUS_RIGHT]) ? 0 : count(
            $answerQuestionReports[AnswerQuestionReportService::STATUS_RIGHT]
        );
    }

    protected function sumSubjectiveScore(array $answerQuestionReports)
    {
        $score = 0;
        $questions = $this->getItemService()->findQuestionsByQuestionIdsIncludeDeleted(
            ArrayToolkit::column($answerQuestionReports, 'question_id')
        );

        foreach ($answerQuestionReports as $answerQuestionReport) {
            if (!empty($questions[$answerQuestionReport['question_id']]) && $this->biz['answer_mode_factory']->create(
                    $questions[$answerQuestionReport['question_id']]['answer_mode']
                )->isSubjective()) {
                $score += $answerQuestionReport['score'];
            }
        }

        return $score;
    }

    protected function sumRightRate(array $answerQuestionReports)
    {
        $totalCount = count($answerQuestionReports);

        $answerQuestionReports = ArrayToolkit::group($answerQuestionReports, 'status');

        $rightCount = empty($answerQuestionReports[AnswerQuestionReportService::STATUS_RIGHT]) ? 0 : count(
            $answerQuestionReports[AnswerQuestionReportService::STATUS_RIGHT]
        );

        return empty($totalCount) ? 0 : round($rightCount / $totalCount * 100, 1);
    }

    protected function sumScore(array $answerQuestionReports)
    {
        return array_sum(ArrayToolkit::column($answerQuestionReports, 'score'));
    }

    protected function isFinished(array $answerQuestionReports, $answerScene)
    {
        if (0 == $answerScene['manual_marking']) {
            return true;
        }

        $answerQuestionReports = ArrayToolkit::group($answerQuestionReports, 'status');

        return empty($answerQuestionReports[AnswerQuestionReportService::STATUS_REVIEWING]);
    }

    protected function getAnswerQuestionReportsByAssessmentReport(array $assessmentReport)
    {
        $answerQuestionReports = [];

        foreach ($assessmentReport['section_reports'] as $sectionReport) {
            foreach ($sectionReport['item_reports'] as $itemReport) {
                foreach ($itemReport['question_reports'] as $questionReport) {
                    $answerQuestionReports[] = [
                        'identify' => $assessmentReport['answer_record_id'] . '_' . $questionReport['id'],
                        'total_score' => empty($questionReport['total_score']) ? 0.0 : $questionReport['total_score'],
                        'answer_record_id' => $assessmentReport['answer_record_id'],
                        'assessment_id' => $assessmentReport['id'],
                        'section_id' => $sectionReport['id'],
                        'item_id' => $itemReport['id'],
                        'question_id' => $questionReport['id'],
                        'score' => empty($questionReport['score']) ? 0.0 : $questionReport['score'],
                        'status' => empty($questionReport['status']) ? '' : $questionReport['status'],
                        'response' => empty($questionReport['response']) ? [] : $questionReport['response'],
                    ];
                }
            }
        }

        return $answerQuestionReports;
    }

    protected function getAnswerQuestionReportsAndAttachmentsByAssessmentResponse(array $assessmentResponse, $reviewedQuestions = [])
    {
        $answerQuestionReports = [];
        $attachments = [];
        $reviewedQuestions = ArrayToolkit::index($reviewedQuestions, 'question_id');

        foreach ($assessmentResponse['section_responses'] as $sectionResponse) {
            foreach ($sectionResponse['item_responses'] as $itemResponse) {
                foreach ($itemResponse['question_responses'] as $questionResponse) {
                    if (!empty($reviewedQuestions[$questionResponse['question_id']])) {
                        continue;
                    }

                    $answerQuestionReports[] = [
                        'identify' => $assessmentResponse['answer_record_id'] . '_' . $questionResponse['question_id'],
                        'answer_record_id' => $assessmentResponse['answer_record_id'],
                        'assessment_id' => $assessmentResponse['assessment_id'],
                        'section_id' => $sectionResponse['section_id'],
                        'item_id' => $itemResponse['item_id'],
                        'question_id' => $questionResponse['question_id'],
                        'response' => $questionResponse['response'],
                    ];
                    if (!empty($questionResponse['attachments'])) {
                        foreach ($questionResponse['attachments'] as $attachment) {
                            $attachments[] = [
                                'id' => $attachment['id'],
                                'module' => $attachment['module'],
                                'question_id' => $questionResponse['question_id'],
                            ];
                        }
                    }
                }
            }
        }

        return [$answerQuestionReports, $attachments];
    }

    protected function getAttachmentsFromAssessmentResponse(array $assessmentResponse)
    {
        $attachments = [];
        foreach ($assessmentResponse['section_responses'] as $sectionResponse) {
            foreach ($sectionResponse['item_responses'] as $itemResponse) {
                foreach ($itemResponse['question_responses'] as $questionResponse) {
                    if (!empty($questionResponse['attachments'])) {
                        foreach ($questionResponse['attachments'] as $attachment) {
                            $attachments[] = [
                                'id' => $attachment['id'],
                                'module' => $attachment['module'],
                                'question_id' => $questionResponse['question_id'],
                            ];
                        }
                    }
                }
            }
        }

        return $attachments;
    }

    public function review(array $reviewReport)
    {
        $reviewReport = $this->getValidator()->validate($reviewReport, [
            'report_id' => ['required', 'integer'],
            'comment' => [],
            'grade' => [],
            'question_reports' => ['required', 'array'],
        ]);

        $answerReport = $this->getAnswerReportService()->getSimple($reviewReport['report_id']);
        if (empty($answerReport)) {
            throw new AnswerReportException('Answer report not found.', ErrorCode::ANSWER_REPORT_NOTFOUND);
        }

        $answerRecord = $this->getAnswerRecordService()->get($answerReport['answer_record_id']);
        if (AnswerService::ANSWER_RECORD_STATUS_REVIEWING != $answerRecord['status']) {
            throw new AnswerException('Answer report cannot review.', ErrorCode::ANSWER_RECORD_CANNOT_REVIEW);
        }

        $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
        $reviewQuestionReports = ArrayToolkit::index($reviewReport['question_reports'], 'id');
        $questionReportIds = ArrayToolkit::column($reviewReport['question_reports'], 'id');
        if ($questionReportIds) {
            $conditions = [
                'ids' => $questionReportIds,
                'answer_record_id' => $answerRecord['id'],
            ];
            $questionReports = $this->getAnswerQuestionReportService()->search(
                $conditions,
                [],
                0,
                $this->getAnswerQuestionReportService()->count($conditions)
            );
            $assessmentQuestions = $this->getAssessmentService()->findAssessmentQuestions($answerRecord['assessment_id']);
            foreach ($questionReports as &$questionReport) {
                $questionReport['comment'] = empty($reviewQuestionReports[$questionReport['id']]['comment']) ? '' : $this->biz['item_bank_html_helper']->purify(
                    $reviewQuestionReports[$questionReport['id']]['comment']
                );

                list($score, $status) = $this->getQuestionReportScoreAndStatus(
                    $answerScene,
                    $questionReport,
                    empty($reviewQuestionReports[$questionReport['id']]) ? array() : $reviewQuestionReports[$questionReport['id']],
                    empty($assessmentQuestions[$questionReport['question_id']]) ? array() : $assessmentQuestions[$questionReport['question_id']]
                );
                $questionReport['score'] = $score;
                $questionReport['status'] = $status;
                $questionReport['total_score'] = empty($assessmentQuestions['score']) ? 0 : $assessmentQuestions['score'];
            }
        }

        try {
            $this->beginTransaction();

            if (!empty($questionReports)) {
                $this->getAnswerQuestionReportService()->batchUpdate($questionReports);
            }

            $this->getAnswerRecordService()->update(
                $answerReport['answer_record_id'],
                [
                    'status' => AnswerService::ANSWER_RECORD_STATUS_FINISHED,
                ]
            );

            $answerQuestionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId(
                $answerReport['answer_record_id']
            );
            $subjectiveScore = $this->sumSubjectiveScore($answerQuestionReports);
            $score = $this->sumScore($answerQuestionReports);
            $answerReport = $this->getAnswerReportService()->update($answerReport['id'], [
                'review_user_id' => empty($this->biz['user']['id']) ? 0 : $this->biz['user']['id'],
                'review_time' => time(),
                'score' => $score,
                'right_rate' => $this->sumRightRate($answerQuestionReports),
                'right_question_count' => $this->getRightQuestionCount($answerQuestionReports),
                'subjective_score' => $subjectiveScore,
                'objective_score' => $score - $subjectiveScore,
                'grade' => empty($reviewReport['grade']) ? 'unpassed' : $reviewReport['grade'],
                'comment' => empty($reviewReport['comment']) ? '' : $reviewReport['comment'],
            ]);

            $this->getAnswerSceneService()->update(
                $answerScene['id'],
                ['name' => $answerScene['name'], 'last_review_time' => time()]
            );

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        $this->dispatch('answer.finished', $answerReport);

        return $answerReport;
    }

    public function reviewSingleAnswerByManual($answerRecordId, $params)
    {
        $reviewedQuestion = $this->getAnswerReviewedQuestionService()->getByAnswerRecordIdAndQuestionId($answerRecordId, $params['question_id']);
        if (empty($reviewedQuestion)) {
            throw new AnswerException('该题还未提交，不能批阅', ErrorCode::ANSWER_NOT_SUBMIT);
        }
        if ($reviewedQuestion['is_reviewed']) {
            throw new AnswerException('该题已批阅，不能再次批阅', ErrorCode::ANSWER_REVIEWED);
        }

        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        $questionReport = $this->getAnswerQuestionReportService()->getByAnswerRecordIdAndQuestionId($answerRecordId, $params['question_id']);
        if (empty($questionReport)) {
            throw new AnswerReportException('Answer report not found.', ErrorCode::ANSWER_REPORT_NOTFOUND);
        }
        if ($questionReport['status'] == AnswerQuestionReportService::STATUS_NOANSWER) {
            $params['status'] == AnswerQuestionReportService::STATUS_WRONG;
        }

        try {
            $this->beginTransaction();

            $answerQuestionReport = $this->getAnswerQuestionReportService()->updateAnswerQuestionReport($questionReport['id'], ['status' => $params['status']]);

            $answerReviewedQuestion = $this->getAnswerReviewedQuestionService()->getByAnswerRecordIdAndQuestionId($answerRecord['id'], $questionReport['question_id']);
            $this->getAnswerReviewedQuestionService()->updateAnswerReviewedQuestion($answerReviewedQuestion['id'], ['is_reviewed' => 1]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $answerQuestionReport;
    }

    public function finishAnswer($answerRecordId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);

        try {
            $this->beginTransaction();

            $this->generateNoAnswerQuestionReports($answerRecord);
            $answerQuestionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecord['id']);
            list($answerRecord) = $this->generateAnswerReport($answerQuestionReports, $answerRecord);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        $this->dispatch('answer.submitted', $answerRecord);

        return $answerRecord;
    }

    public function getSubmittedQuestions($answerRecordId)
    {
        $reviewedQuestions = $this->getAnswerReviewedQuestionService()->findByAnswerRecordId($answerRecordId);
        if (empty($reviewedQuestions)) {
            return [];
        }
        $reviewedQuestions = ArrayToolkit::index($reviewedQuestions, 'question_id');

        $answerQuestionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecordId);
        if (empty($answerQuestionReports)) {
            return [];
        }
        $answerQuestionReports = ArrayToolkit::index($answerQuestionReports, 'question_id');

        $questions = $this->getItemService()->findQuestionsByQuestionIdsIncludeDeleted(array_column($reviewedQuestions, 'question_id'));
        if (empty($questions)) {
            return [];
        }

        $submittedQuestions = [];
        foreach ($reviewedQuestions as $questionId => $reviewedQuestion) {
            $submittedQuestions[] = [
                'questionId' => $questionId,
                'answer' => $questions[$questionId]['answer'],
                'analysis' => $this->filterHtml($questions[$questionId]['analysis']),
                'manualMarking' => $reviewedQuestion['is_reviewed'] ? 0 : 1,
                'status' => $answerQuestionReports[$questionId]['status'],
                'response' => $answerQuestionReports[$questionId]['response'],
            ];
        }

        return $submittedQuestions;
    }

    private function modifyAssessmentIfItemDeleted($assessmentId)
    {
        $toDeleteSectionItems = $this->getSectionItemService()->findDeletedAssessmentSectionItems($assessmentId);
        if (empty($toDeleteSectionItems)) {
            return;
        }
        $assessmentSnapshots = $this->getAssessmentService()->createAssessmentSnapshotsIncludeSectionsAndItems([$assessmentId]);
        $this->getAnswerRecordService()->replaceAssessmentsWithSnapshotAssessments($assessmentSnapshots);
        $this->getAnswerReportService()->replaceAssessmentsWithSnapshotAssessments($assessmentSnapshots);
        $this->getAssessmentService()->modifyAssessmentsAndSectionsWithToDeleteSectionItems($toDeleteSectionItems);
        $this->getSectionItemService()->deleteAssessmentSectionItems($toDeleteSectionItems);
    }

    private function generateNoAnswerQuestionReports($answerRecord)
    {
        $assessmentQuestions = $this->getAssessmentService()->findAssessmentQuestions($answerRecord['assessment_id']);

        $answerReviewedQuestions = $this->getAnswerReviewedQuestionService()->findByAnswerRecordId($answerRecord['id']);
        $answerReviewedQuestions = ArrayToolkit::index($answerReviewedQuestions, 'question_id');

        $answerQuestionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecord['id']);
        $answerQuestionReports = ArrayToolkit::index($answerQuestionReports, 'question_id');

        $createQuestionReports = [];
        $updateQuestionReports = [];
        foreach ($assessmentQuestions as $questionId => $assessmentQuestion) {
            if (empty($answerQuestionReports[$questionId])) {
                $createQuestionReports[] = [
                    'identify' => $answerRecord['id'] . '_' . $questionId,
                    'answer_record_id' => $answerRecord['id'],
                    'assessment_id' => $answerRecord['assessment_id'],
                    'section_id' => $assessmentQuestion['section_id'],
                    'item_id' => $assessmentQuestion['item_id'],
                    'question_id' => $questionId,
                    'score' => '0',
                    'total_score' => $assessmentQuestion['score'],
                    'response' => [],
                    'status' => AnswerQuestionReportService::STATUS_NOANSWER,
                    'comment' => '',
                    'revise' => [],
                ];
                continue;
            }
            if (empty($answerReviewedQuestions[$questionId])) {
                $updateQuestionReports[] = [
                    'identify' => $answerQuestionReports[$questionId]['identify'],
                    'response' => [],
                    'status' => AnswerQuestionReportService::STATUS_NOANSWER,
                ];
            }
        }

        $this->getAnswerQuestionReportService()->batchCreate($createQuestionReports);
        $this->getAnswerQuestionReportService()->batchUpdate($updateQuestionReports);
    }

    public function reviseFillAnswer($answerRecordId, $fillData)
    {
        if (empty($fillData)) {
            return;
        }
        try {
            $this->beginTransaction();
            $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
            $answerReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecordId);
            $answerReports = ArrayToolkit::index($answerReports, 'item_id');
            $answerReportQuestion = $answerReports[$fillData['item_id']];
            $answerReportQuestion = $this->processFillQuestionReviseScore(
                $answerRecord,
                $answerReportQuestion,
                $fillData
            );
            $answerReports[$answerReportQuestion['item_id']] = $answerReportQuestion;
            $answerReport = $this->processReviseAnswerReport($answerRecord, $answerReports);
            $this->dispatch('answer.finished', $answerReport);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();

            return false;
            throw $e;
        }

        return true;
    }

    protected function processReviseAnswerReport($answerRecord, $answerReports)
    {
        $answerReports = array_values($answerReports);
        $subjectiveScore = $this->sumSubjectiveScore($answerReports);
        $score = $this->sumScore($answerReports);

        return $this->getAnswerReportService()->update($answerRecord['answer_report_id'], [
            'total_score' => $this->sumTotalScore($answerReports),
            'score' => $score,
            'subjective_score' => $subjectiveScore,
            'objective_score' => $score - $subjectiveScore,
            'right_rate' => $this->sumRightRate($answerReports),
            'right_question_count' => $this->getRightQuestionCount($answerReports),
        ]);
    }

    protected function processFillQuestionReviseScore($answerRecord, $answerReportQuestion, $fillData)
    {
        $item = $this->getSectionItemService()->getItemByAssessmentIdAndItemId(
            $answerReportQuestion['assessment_id'],
            $fillData['item_id']
        );
        $questions = \AppBundle\Common\ArrayToolkit::index($item['score_rule'], 'question_id');
        $questionRule = $questions[$answerReportQuestion['question_id']]['rule'];
        $questionRule = \AppBundle\Common\ArrayToolkit::index($questionRule, 'name');
        $question = $this->getItemService()->getQuestionIncludeDeleted($answerReportQuestion['question_id']);
        $answers = [];
        foreach ($question['answer'] as $key => $answer) {
            $answers[$key] = explode('|', $answer);
        }
        $result = [];
        foreach ($answerReportQuestion['response'] as $key => $response) {
            $result[$key] = !empty($response) && in_array($response, $answers[$key]) ? 1 : 0;
        }
        $rightCount = 0;
        $revise = $answerReportQuestion['revise'];
        foreach ($fillData['answer'] as $key => $value) {
            if (!empty($revise[$key])) {
                ++$rightCount;
                continue;
            } else {
                $revise[$key] = 0;
            }
            if (!empty($result[$key]) || (empty($result[$key]) && !empty($value))) {
                $revise[$key] = 1;
                ++$rightCount;
                continue;
            }
        }
        $rule = $questionRule['part_right'];
        if ('question' == $rule['score_rule']['scoreType']) {
            $score = $rightCount == count($answers) ? $item['score'] : 0.0;
        }
        if ('option' == $rule['score_rule']['scoreType']) {
            $totle = $rule['score_rule']['otherScore'] * $rightCount;
            $score = $totle >= $answerReportQuestion['score'] && $totle <= $answerReportQuestion['total_score'] ? $totle : $answerReportQuestion['score'];
        }
        $status = $answerReportQuestion['status'];
        if ($rightCount == count($answers)) {
            $status = AnswerQuestionReportService::STATUS_RIGHT;
            $this->getWrongQuestionDao()->batchDelete(
                [
                    'item_id' => $item['id'],
                    'user_id' => $answerRecord['user_id'],
                    'answer_scene_id' => $answerRecord['answer_scene_id'],
                ]
            );
        }

        return $this->getAnswerQuestionReportDao()->update($answerReportQuestion['id'], [
            'score' => $score,
            'revise' => $revise,
            'status' => $status,
        ]);
    }

    protected function getQuestionReportScoreAndStatus(
        $answerScene,
        $questionReport,
        $reviewQuestionReport,
        $assessmentQuestion
    )
    {
        if (empty($reviewQuestionReport) || empty($assessmentQuestion)) {
            return [0, AnswerQuestionReportService::STATUS_NOANSWER];
        }

        if (0 == $answerScene['need_score']) {
            if (empty($reviewQuestionReport['status'])) {
                $status = AnswerQuestionReportService::STATUS_RIGHT;
            } else {
                $status = AnswerQuestionReportService::STATUS_WRONG == $reviewQuestionReport['status'] ? AnswerQuestionReportService::STATUS_WRONG : AnswerQuestionReportService::STATUS_RIGHT;
            }

            return [0, $status];
        }

        $reviewQuestionReport['score'] = empty($reviewQuestionReport['score']) ? 0 : $reviewQuestionReport['score'];
        if (0 == $reviewQuestionReport['score']) {
            $score = 0;
            $status = AnswerQuestionReportService::STATUS_WRONG;
        } elseif ($reviewQuestionReport['score'] >= $assessmentQuestion['score']) {
            $score = $assessmentQuestion['score'];
            $status = AnswerQuestionReportService::STATUS_RIGHT;
        } else {
            $score = $reviewQuestionReport['score'];
            $status = AnswerQuestionReportService::STATUS_PART_RIGHT;
        }

        return [$score, $status];
    }

    public function getAssessmentResponseByAnswerRecordId($answerRecordId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        if (empty($answerRecord)) {
            throw new AnswerException('Answer record not found.', ErrorCode::ANSWER_RECORD_NOTFOUND);
        }

        $answerQuestionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecordId);
        if (!empty($answerQuestionReports)) {
            $answerQuestionReports = $this->getAnswerReportService()->wrapperAnswerQuestionReports(
                $answerRecord['id'],
                $answerQuestionReports
            );
        }

        $attachments = $this->getAttachmentService()->findAttachmentsByTargetIdsAndTargetType(
            ArrayToolkit::column($answerQuestionReports, 'id'),
            AttachmentService::ANSWER_TYPE
        );

        $assessmentResponse = [
            'assessment_id' => $answerRecord['assessment_id'],
            'answer_record_id' => $answerRecord['id'],
            'used_time' => $answerRecord['used_time'],
            'section_responses' => [],
        ];

        $tagQuestionIds = $answerRecord['isTag']
            ? $this->getAnswerQuestionTagService()->getTagQuestionIdsByAnswerRecordId($answerRecord['id'])
            : [];
        $sectionResponses = ArrayToolkit::group($answerQuestionReports, 'section_id');
        $attachments = ArrayToolkit::group($attachments, 'target_id');
        foreach ($sectionResponses as $sectionId => $sectionResponse) {
            $itemResponses = ArrayToolkit::group($sectionResponse, 'item_id');
            foreach ($itemResponses as $itemId => &$itemResponse) {
                foreach ($itemResponse as &$questionResponse) {
                    $questionResponse = [
                        'question_id' => intval($questionResponse['question_id']),
                        'response' => $questionResponse['response'],
                        'attachments' => empty($attachments[$questionResponse['id']]) ? [] : $attachments[$questionResponse['id']],
                        'isTag' => in_array($questionResponse['question_id'], $tagQuestionIds),
                    ];
                }
                $itemResponse = [
                    'item_id' => $itemId,
                    'question_responses' => array_values($itemResponse),
                ];
            }
            $sectionResponse = [
                'section_id' => $sectionId,
                'item_responses' => array_values($itemResponses),
            ];
            $assessmentResponse['section_responses'][] = $sectionResponse;
        }

        return $assessmentResponse;
    }

    public function pauseAnswer(array $assessmentResponse)
    {
        try {
            $this->beginTransaction();

            $assessmentResponse = $this->saveAnswer($assessmentResponse);

            $answerRecord = $this->getAnswerRecordService()->update(
                $assessmentResponse['answer_record_id'],
                ['status' => AnswerService::ANSWER_RECORD_STATUS_DOING]
            );

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        $this->dispatch('answer.paused', $answerRecord);

        return $answerRecord;
    }

    public function continueAnswer($answerRecordId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);

        if (empty($answerRecord)) {
            throw new AnswerException('Answer record not found.', ErrorCode::ANSWER_RECORD_NOTFOUND);
        }

        if (AnswerService::ANSWER_RECORD_STATUS_DOING == $answerRecord['status']) {
            return $this->getAnswerRecordService()->update(
                $answerRecordId,
                [
                    'admission_ticket' => $this->generateAdmissionTicket(),
                ]
            );
        }

        if (in_array($answerRecord['status'], [AnswerService::ANSWER_RECORD_STATUS_REVIEWING, AnswerService::ANSWER_RECORD_STATUS_FINISHED])) {
            throw new AnswerException('你已提交过答题，当前页面无法重复提交', ErrorCode::ANSWER_NODOING);
        }

        $this->dispatch('answer.continued', $answerRecord);

        return $this->getAnswerRecordService()->update(
            $answerRecordId,
            [
                'status' => AnswerService::ANSWER_RECORD_STATUS_DOING,
                'admission_ticket' => $this->generateAdmissionTicket(),
            ]
        );
    }

    public function saveAnswer(array $assessmentResponse)
    {
        $assessmentResponse = $this->convertAssessmentResponse($assessmentResponse);
        $assessmentResponse = $this->validateAssessmentResponse($assessmentResponse);
        $assessmentResponse = $this->getAnswerRandomSeqService()->restoreOptionsToOriginalSeqIfNecessary($assessmentResponse);

        try {
            $this->beginTransaction();

            $answerRecord = $this->getAnswerRecordService()->get($assessmentResponse['answer_record_id']);
            if (ExerciseMode::SUBMIT_SINGLE == $answerRecord['exercise_mode']) {
                $reviewedQuestions = $this->getAnswerReviewedQuestionService()->findByAnswerRecordId($answerRecord['id']);
            }

            list($answerQuestionReports, $attachments) = $this->getAnswerQuestionReportsAndAttachmentsByAssessmentResponse(
                $assessmentResponse,
                $reviewedQuestions ?? []
            );
            $this->saveAnswerQuestionReport($answerQuestionReports, $answerRecord['id']);

            $this->saveAnswerQuestionTag($assessmentResponse, $answerRecord);

            $this->updateAttachmentsTarget($answerRecord['id'], $attachments);

            //判断模拟考试应该取当前时间减去开始时间
            if (self::EXAM_MODE_SIMULATION == $answerRecord['exam_mode']) {
                $assessmentResponse['used_time'] = time() - $answerRecord['created_time'];
            }

            $this->getAnswerRecordService()->update($answerRecord['id'], [
                'used_time' => $assessmentResponse['used_time'],
            ]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        $this->dispatch('answer.saved', $assessmentResponse);

        return $assessmentResponse;
    }

    private function convertAssessmentResponse($assessmentResponse)
    {
        $answerRecord = $this->getAnswerRecordService()->get($assessmentResponse['answer_record_id']);
        if (empty($answerRecord)) {
            return $assessmentResponse;
        }
        if ($answerRecord['assessment_id'] == $assessmentResponse['assessment_id']) {
            return $assessmentResponse;
        }
        $assessmentSnapshot = $this->getAssessmentService()->getAssessmentSnapshotBySnapshotAssessmentId($answerRecord['assessment_id']);
        if (empty($assessmentSnapshot) || $assessmentSnapshot['origin_assessment_id'] != $assessmentResponse['assessment_id']) {
            return $assessmentResponse;
        }
        $assessmentResponse['assessment_id'] = $answerRecord['assessment_id'];
        foreach ($assessmentResponse['section_responses'] as &$sectionResponse) {
            $sectionResponse['section_id'] = $assessmentSnapshot['sections_snapshot'][$sectionResponse['section_id']];
        }

        return $assessmentResponse;
    }

    protected function saveAnswerQuestionTag(array $assessmentResponse, $answerRecord)
    {
        $questionIds = [];
        foreach ($assessmentResponse['section_responses'] as $sectionResponse) {
            foreach ($sectionResponse['item_responses'] as $itemResponse) {
                foreach ($itemResponse['question_responses'] as $questionResponse) {
                    if (!empty($questionResponse['isTag'])) {
                        $questionIds[] = $questionResponse['question_id'];
                    }
                }
            }
        }

        if (empty($questionIds)) {
            if (!empty($answerRecord['isTag'])) {
                $this->getAnswerRecordService()->update($answerRecord['id'], ['isTag' => 0]);
                $this->getAnswerQuestionTagService()->deleteByAnswerRecordId($answerRecord['id']);
            }

            return;
        }
        if (!empty($answerRecord['isTag'])) {
            $this->getAnswerQuestionTagService()->updateByAnswerRecordId($answerRecord['id'], $questionIds);
        } else {
            $this->getAnswerQuestionTagService()->createAnswerQuestionTag($answerRecord['id'], $questionIds);
            $this->getAnswerRecordService()->update($answerRecord['id'], ['isTag' => 1]);
        }
    }

    protected function updateAttachmentsTarget($answerRecordId, $attachments)
    {
        $updateFields = [];
        $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecordId);
        $questionReports = ArrayToolkit::index($questionReports, 'question_id');
        foreach ($attachments as $attachment) {
            if (!empty($questionReports[$attachment['question_id']])) {
                $updateFields[] = [
                    'id' => $attachment['id'],
                    'target_id' => $questionReports[$attachment['question_id']]['id'],
                    'module' => $attachment['module'],
                    'target_type' => AttachmentService::ANSWER_MODULE,
                ];
            }
        }

        return $this->getAttachmentService()->batchUpdate($updateFields);
    }

    protected function validateAssessmentResponse(array $assessmentResponse)
    {
        $assessmentResponse = $this->getValidator()->validate($assessmentResponse, [
            'assessment_id' => ['required', 'integer'],
            'answer_record_id' => ['required', 'integer'],
            'used_time' => ['required', 'integer', ['min', 0]],
            'section_responses' => ['required', 'array'],
        ]);

        $answerRecord = $this->getAnswerRecordService()->get($assessmentResponse['answer_record_id']);
        if (empty($answerRecord)) {
            throw new AnswerException('找不到答题记录.', ErrorCode::ANSWER_RECORD_NOTFOUND);
        }

        if ($answerRecord['assessment_id'] != $assessmentResponse['assessment_id']) {
            throw $this->createInvalidArgumentException('assessment_id invalid.');
        }

        if (!in_array($answerRecord['status'], [AnswerRecordStatus::DOING, AnswerRecordStatus::PAUSED])) {
            throw new AnswerException('你已提交过答题，当前页面无法重复提交', ErrorCode::ANSWER_NODOING);
        }

        foreach ($assessmentResponse['section_responses'] as &$sectionResponse) {
            foreach ($sectionResponse['item_responses'] as &$itemResponse) {
                foreach ($itemResponse['question_responses'] as &$questionResponse) {
                    foreach ($questionResponse['response'] as &$response) {
                        $response = trim($response);
                    }
                }
            }
        }

        return $assessmentResponse;
    }

    protected function registerAutoSubmitJob($answerRecord)
    {
        $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);

        if (empty($answerScene['limited_time']) && $answerScene['valid_period_mode'] != 3) {
            return;
        }

        if (self::EXAM_MODE_SIMULATION != $answerRecord['exam_mode']) {
            return;
        }
        $time = time() + $answerScene['limited_time'] * 60 + 120;
        if ($answerScene['valid_period_mode'] == 3) {
            $time = $answerScene['end_time'];
        }
        $autoSubmitJob = [
            'name' => 'AssessmentAutoSubmitJob_' . $answerRecord['id'] . '_' . time(),
            'expression' => $time,
            'class' => 'Biz\Testpaper\Job\AssessmentAutoSubmitJob',
            'args' => ['answerRecordId' => $answerRecord['id']],
        ];

        $this->getSchedulerService()->register($autoSubmitJob);
    }

    protected function saveAnswerQuestionReport($answerQuestionReports, $answerRecordId)
    {
        $existIdentifies = $this->getAnswerQuestionReportService()->search(['answer_record_id' => $answerRecordId], [], 0, PHP_INT_MAX, ['identify']);
        if (empty($existIdentifies)) {
            $this->getAnswerQuestionReportService()->batchCreate($answerQuestionReports);
        }else {
            // 对$answerQuestionReports分类，分成存在的数据和不存在的数据，然后存在的去更新，不存在的去创建
            $existIdentifies = array_column($existIdentifies, 'identify'); // 获取已存在的 identify
            $toCreate = [];
            $toUpdate = [];
            foreach ($answerQuestionReports as $report) {
                if (in_array($report['identify'], $existIdentifies)) {
                    $toUpdate[] = $report;
                } else {
                    $toCreate[] = $report;
                }
            }
            // 对需要创建的数据进行批量创建
            if (!empty($toCreate)) {
                $this->getAnswerQuestionReportService()->batchCreate($toCreate);
            }
            // 对需要更新的数据进行批量更新
            if (!empty($toUpdate)) {
                $this->getAnswerQuestionReportService()->batchUpdate($toUpdate);
            }

        }
    }

    /**
     * @param $assessmentResponse
     * @return mixed
     */
    protected function appendNoAnswerQuestion($assessmentResponse)
    {
        $assessment = $this->getAssessmentService()->showAssessment($assessmentResponse['assessment_id']);
        $allIdentifies = [];
        $sectionResponses = [];
        $assessmentTotalQuestions = 0;
        $allQuestionIds = [];
        foreach ($assessment['sections'] as $section) {
            foreach ($section['items'] as $item) {
                $assessmentTotalQuestions += count($item['questions']);
                foreach ($item['questions'] as $question) {
                    $allIdentifies[] = $assessmentResponse['answer_record_id'] . '_' . $question['id'];
                    $sectionResponses[$question['id']] = $section['id'].'_'.$item['id'];
                    $allQuestionIds[] = $question['id'];
                }
            }
        }
        // todo 需要拿答题快照， 确认共享快照还是独立快照
        if ($assessmentTotalQuestions == $this->countTotalQuestions($assessmentResponse)) {
            return $assessmentResponse;
        }
        $saveDassessmentResponse = $this->getAssessmentResponseByAnswerRecordId($assessmentResponse['answer_record_id']);
        $savedIdentifies = [];
        foreach ($saveDassessmentResponse['section_responses'] as $section_response)  {
            foreach ($section_response['item_responses'] as $item_response) {
                foreach ($item_response['question_responses'] as $question_response) {
                    $identify = $assessmentResponse['answer_record_id'] . '_' . $question_response['question_id'];
                    $savedIdentifies[$identify] = $question_response['response'];
                }
            }
        }
//        $savedQuestionReports = $this->getAnswerQuestionReportService()->search(['answer_record_id' => $assessmentResponse['answer_report_id']], [], 0, PHP_INT_MAX);
//        $savedIdentifies = array_column($savedQuestionReports, 'identify');
//        $answerQuestionReportIndex = ArrayToolkit::index($savedQuestionReports, 'identify');
        $answerResults = [];
        foreach ($assessmentResponse['section_responses'] as $sectionResponse)  {
            foreach ($sectionResponse['item_responses'] as $itemResponse) {
                foreach ($itemResponse['question_responses'] as $questionResponse) {
                    $answerResult['response'] = $questionResponse['response'] ?? [""];
                    $answerResults[$sectionResponse['section_id']][$itemResponse['item_id']][$questionResponse['question_id']] = $answerResult;
                }
            }
        }
        $assessmentSectiones = $this->getAssessmentSectionService()->findSectionsByAssessmentId($assessment['id']);
        $assessmentSectionesIndex = ArrayToolkit::index($assessmentSectiones, 'id');
        $questionsIndex = $this->getItemService()->findQuestionsByQuestionIds($allQuestionIds);
        // 将缺失的问题添加到新的结构中
        foreach ($allIdentifies as $identify) {
            list($answerRecordId, $questionId) = explode('_', $identify);
            list($sectionId, $itemId) = explode('_', $sectionResponses[$questionId]);
            // 判断该section是否已经存在
            if (!isset($newSectionResponses[$sectionId])) {
                $newSectionResponses[$sectionId] = [
                    'section_id' => $sectionId,
                    'item_responses' => []
                ];
            }
            // 判断该item是否已经存在
            if (!isset($newSectionResponses[$sectionId]['item_responses'][$itemId])) {
                $newSectionResponses[$sectionId]['item_responses'][$itemId] = [
                    'item_id' => $itemId,
                    'question_responses' => []
                ];
            }
            $answerResponse = "";
            // 添加缺失的问题
            if (!empty($savedIdentifies[$identify])) {
                $answerResponse = $savedIdentifies[$identify];
            }
            // 把最后提交的结果放到
            if (!empty($answerResults[$sectionId][$itemId][$questionId])) {
                $answerResponse = $answerResults[$sectionId][$itemId][$questionId]['response'];
            }
            // todo 兼容安卓
            $noResponse = [];
            if ($questionsIndex[$questionId]['answer_mode'] == 'single_choice') {
                $noResponse = null;
            }
            if ($questionsIndex[$questionId]['answer_mode'] == 'choice') {
                $noResponse = [];
            }
            if ($questionsIndex[$questionId]['answer_mode'] == 'rich_text') {
                $noResponse = [""];
            }
            if ($questionsIndex[$questionId]['answer_mode'] == 'uncertain_choice') {
                $noResponse = [];
            }
            if ($questionsIndex[$questionId]['answer_mode'] == 'true_false') {
                $noResponse = [""];
            }
            if ($questionsIndex[$questionId]['answer_mode'] == 'text') {
                $question = $this->findQuestion($assessment['sections'], $sectionId, $itemId, $questionId);
                if ($question) {
                    $responsePointsCount = count($question['response_points']);
                    $noResponse = array_fill(0, $responsePointsCount, "");
                } else {
                    $noResponse = [""];
                }
            }
            if ($assessmentSectionesIndex[$sectionId]['name'] == '材料题') {
                $noResponse = [""];
            }

            $newSectionResponses[$sectionId]['item_responses'][$itemId]['question_responses'][] = [
                'question_id' => $questionId,
                'response' => $answerResponse ?? $noResponse
            ];
        }
        // 重建后的section_responses替换掉原有的
        $assessmentResponse['section_responses'] = array_values($newSectionResponses);

        return $assessmentResponse;
    }

    protected function findQuestion($data, $sectionId, $itemId, $questionId) {
        // 遍历 sections
        foreach ($data['sections'] as $section) {
            if ($section['id'] == $sectionId) {
                // 遍历 items
                foreach ($section['items'] as $item) {
                    if ($item['id'] == $itemId) {
                        // 遍历 questions
                        foreach ($item['questions'] as $question) {
                            if ($question['id'] == $questionId) {
                                return $question;
                            }
                        }
                    }
                }
            }
        }
        return null; // 如果没有找到，返回 null
    }

    function countTotalQuestions($data) {
        $totalQuestions = 0;
        // 遍历 sections
        foreach ($data['section_responses'] as $section) {
            // 遍历 items
            foreach ($section['item_responses'] as $item) {
                // 统计每个 item 的 questions 数量
                $totalQuestions += count($item['question_responses']);
            }
        }

        return $totalQuestions;
    }

    protected function filterHtml($text)
    {
        preg_match_all('/\<img.*?src\s*=\s*[\'\"](.*?)[\'\"]/i', $text, $matches);
        if (empty($matches)) {
            return $text;
        }

        foreach ($matches[1] as $url) {
            $text = str_replace($url, AssetHelper::uriForPath($url), $text);
        }

        return $text;
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Assessment\Service\AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerQuestionReportService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Item\Service\AttachmentService
     */
    protected function getAttachmentService()
    {
        return $this->biz->service('ItemBank:Item:AttachmentService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Item\Service\ItemService
     */
    protected function getItemService()
    {
        return $this->biz->service('ItemBank:Item:ItemService');
    }

    /**
     * @return AnswerRandomSeqService
     */
    protected function getAnswerRandomSeqService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerRandomSeqService');
    }

    /**
     * @return AssessmentSectionService
     */
    protected function getAssessmentSectionService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentSectionService');
    }

    /**
     * @return AssessmentSectionItemService
     */
    protected function getSectionItemService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentSectionItemService');
    }

    protected function getAnswerQuestionReportDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerQuestionReportDao');
    }

    /**
     * @return WrongQuestionDao
     */
    protected function getWrongQuestionDao()
    {
        return $this->biz->dao('WrongBook:WrongQuestionDao');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }

    /**
     * @return Question
     */
    protected function getQuestionProcessor()
    {
        return $this->biz['question_processor'];
    }

    /**
     * @return AnswerReviewedQuestionService
     */
    protected function getAnswerReviewedQuestionService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerReviewedQuestionService');
    }

    /**
     * @return AnswerQuestionTagService
     */
    protected function getAnswerQuestionTagService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerQuestionTagService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->biz->service('Activity:TestpaperActivityService');
    }
}
