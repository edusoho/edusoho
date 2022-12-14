<?php

namespace Codeages\Biz\ItemBank\Answer\Service\Impl;

use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\System\Service\LogService;
use Biz\Testpaper\Job\AssessmentAutoSubmitJob;
use Biz\User\UserException;
use Biz\WrongBook\Dao\WrongQuestionDao;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerException;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerReportException;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerSceneException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\BaseService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;
use Codeages\Biz\ItemBank\Item\Service\AttachmentService;
use Ramsey\Uuid\Uuid;

class AnswerServiceImpl extends BaseService implements AnswerService
{
    public function startAnswer($answerSceneId, $assessmentId, $userId)
    {
        if (!$this->getAnswerSceneService()->canStart($answerSceneId, $userId)) {
            throw new AnswerSceneException('AnswerScene did not start.', ErrorCode::ANSWER_SCENE_NOTSTART);
        }

        $answerRecord = $this->getAnswerRecordService()->create([
            'answer_scene_id' => $answerSceneId,
            'assessment_id' => $assessmentId,
            'user_id' => $userId,
            'admission_ticket' => $this->generateAdmissionTicket(),
        ]);

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
        $assessmentResponse = $this->validateAssessmentResponse($assessmentResponse);
        $attachments = $this->getAttachmentsFromAssessmentResponse($assessmentResponse);
        $assessmentReport = $this->getAssessmentService()->review(
            $assessmentResponse['assessment_id'],
            $assessmentResponse['section_responses']
        );
        $assessmentReport['answer_record_id'] = $assessmentResponse['answer_record_id'];
        $answerQuestionReports = $this->getAnswerQuestionReportsByAssessmentReport($assessmentReport);

        $answerRecord = $this->getAnswerRecordService()->get($assessmentReport['answer_record_id']);
        $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
        $canFinished = $this->canFinished($answerQuestionReports, $answerScene);

        try {
            $this->beginTransaction();

            if ($this->getAnswerQuestionReportService()->count(
                ['answer_record_id' => $assessmentResponse['answer_record_id']]
            )) {
                $this->getAnswerQuestionReportService()->batchUpdate($answerQuestionReports);
            } else {
                $this->getAnswerQuestionReportService()->batchCreate($answerQuestionReports);
            }

            $subjectiveScore = $this->sumSubjectiveScore($answerQuestionReports);
            $score = $this->sumScore($answerQuestionReports);

            $answerReport = $this->getAnswerReportService()->create([
                'user_id' => $answerRecord['user_id'],
                'assessment_id' => $assessmentResponse['assessment_id'],
                'answer_record_id' => $assessmentResponse['answer_record_id'],
                'total_score' => $this->sumTotalScore($answerQuestionReports),
                'score' => $score,
                'subjective_score' => $subjectiveScore,
                'objective_score' => $score - $subjectiveScore,
                'right_rate' => $this->sumRightRate($answerQuestionReports),
                'right_question_count' => $this->getRightQuestionCount($answerQuestionReports),
                'review_time' => $canFinished ? time() : 0,
            ]);

            $this->updateAttachmentsTarget($assessmentResponse['answer_record_id'], $attachments);

            $answerRecord = $this->getAnswerRecordService()->update(
                $assessmentResponse['answer_record_id'],
                [
                    'answer_report_id' => $answerReport['id'],
                    'status' => $canFinished ? AnswerService::ANSWER_RECORD_STATUS_FINISHED : AnswerService::ANSWER_RECORD_STATUS_REVIEWING,
                    'end_time' => time(),
                    'used_time' => $assessmentResponse['used_time'],
                ]
            );

            if ($canFinished) {
                $this->getAnswerSceneService()->update(
                    $answerScene['id'],
                    ['name' => $answerScene['name'], 'last_review_time' => time()]
                );
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        $this->dispatch('answer.submitted', $answerRecord);

        return $answerRecord;
    }

    public function buildAssessmentResponse($answerRecordId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
        $assessmentResponse = ['answer_record_id' => $answerRecordId, 'assessment_id' => $answerRecord['assessment_id']];
        $answerQuestionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecordId);
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
        $assessmentResponse['used_time'] = $answerScene['limited_time'] * 60;
        return $assessmentResponse;
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
        $questions = $this->getItemService()->findQuestionsByQuestionIds(
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

    protected function canFinished(array $answerQuestionReports, $answerScene)
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
                        'identify' => $assessmentReport['answer_record_id'].'_'.$questionReport['id'],
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

    protected function getAnswerQuestionReportsAndAttachmentsByAssessmentResponse(array $assessmentResponse)
    {
        $answerQuestionReports = [];
        $attachments = [];

        foreach ($assessmentResponse['section_responses'] as $sectionResponse) {
            foreach ($sectionResponse['item_responses'] as $itemResponse) {
                foreach ($itemResponse['question_responses'] as $questionResponse) {
                    $answerQuestionReports[] = [
                        'identify' => $assessmentResponse['answer_record_id'].'_'.$questionResponse['question_id'],
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

    public function review(array $reviewReport, $userId)
    {
        $reviewReport = $this->getValidator()->validate($reviewReport, [
            'report_id' => ['required', 'integer'],
            'comment' => [],
            'grade' => [],
            'question_reports' => ['required', 'array'],
        ]);

        $answerReport = $this->getAnswerReportService()->getSimple($reviewReport['report_id']);
        if (empty($answerReport)) {
            throw new AnswerReportException('Answer report not found.', ErrorCode::ANSWER_RECORD_NOTFOUND);
        }

        $answerRecord = $this->getAnswerRecordService()->get($answerReport['answer_record_id']);
        if (AnswerService::ANSWER_RECORD_STATUS_REVIEWING != $answerRecord['status']) {
            throw new AnswerException('Answer report cannot review.', ErrorCode::ANSWER_RECORD_CANNOT_REVIEW);
        }

        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerRecord['answer_scene_id']);

        $courseSetMember = array_column($this->getCourseMemberService()->findCourseSetTeachersAndAssistant($activity['fromCourseSetId']), 'userId');
        if(!in_array($userId, $courseSetMember)) {
            throw UserException::PERMISSION_DENIED();
        }

        $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
        $reviewQuestionReports = ArrayToolkit::index($reviewReport['question_reports'], 'id');
        $questionReportIds = ArrayToolkit::column($reviewReport['question_reports'], 'id');
        if (empty($questionReportIds)) {
            goto changeReportStatus;
        }
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

        changeReportStatus:
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
        $question = $this->getQuestionDao()->get($answerReportQuestion['question_id']);
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
                $rightCount++;
                continue;
            } else {
                $revise[$key] = 0;
            }
            if (!empty($result[$key]) || (empty($result[$key]) && !empty($value))) {
                $revise[$key] = 1;
                $rightCount++;
                continue;
            }
        }
        $rule = $questionRule['part_right'];
        if ($rule['score_rule']['scoreType'] == 'question') {
            $score = $rightCount == count($answers) ? $item['score'] : 0.0;
        }
        if ($rule['score_rule']['scoreType'] == 'option') {
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
    ) {
        if (empty($reviewQuestionReport) || empty($assessmentQuestion)) {
            return [0, AnswerQuestionReportService::STATUS_NOANSWER];
        }

        if (0 == $answerScene['need_score']) {
            if (empty($reviewQuestionReport['status'])) {
                $status = AnswerQuestionReportService::STATUS_RIGHT;
            } else {
                $status = $reviewQuestionReport['status'] == AnswerQuestionReportService::STATUS_WRONG ? AnswerQuestionReportService::STATUS_WRONG : AnswerQuestionReportService::STATUS_RIGHT;
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
                ['status' => AnswerService::ANSWER_RECORD_STATUS_PAUSED]
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
        $assessmentResponse = $this->validateAssessmentResponse($assessmentResponse);

        try {
            $this->beginTransaction();

            list($answerQuestionReports, $attachments) = $this->getAnswerQuestionReportsAndAttachmentsByAssessmentResponse(
                $assessmentResponse
            );
            if ($this->getAnswerQuestionReportService()->count(
                ['answer_record_id' => $assessmentResponse['answer_record_id']]
            )) {
                $this->getAnswerQuestionReportService()->batchUpdate($answerQuestionReports);
            } else {
                $this->getAnswerQuestionReportService()->batchCreate($answerQuestionReports);
            }

            $this->updateAttachmentsTarget($assessmentResponse['answer_record_id'], $attachments);

            $this->getAnswerRecordService()->update($assessmentResponse['answer_record_id'], [
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

        if (empty($this->getAnswerRecordService()->get($assessmentResponse['answer_record_id']))) {
            throw new AnswerException('找不到答题记录.', ErrorCode::ANSWER_RECORD_NOTFOUND);
        }

        if (!in_array($answerRecord['status'],[AnswerService::ANSWER_RECORD_STATUS_DOING,AnswerService::ANSWER_RECORD_STATUS_PAUSED])) {
            throw new AnswerException('你已提交过答题，当前页面无法重复提交', ErrorCode::ANSWER_NODOING);
        }

        if ($answerRecord['assessment_id'] != $assessmentResponse['assessment_id']) {
            throw $this->createInvalidArgumentException('assessment_id invalid.');
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

    protected function registerAutoSubmitJob($answerRecord){
        $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);

        if (empty($answerScene['limited_time'])){
            return;
        }
        $autoSubmitJob = [
            'name' => 'AssessmentAutoSubmitJob_' . $answerRecord['id'] . '_' . time(),
            'expression' => time() + $answerScene['limited_time'] * 60 + 120,
            'class' => 'Biz\Testpaper\Job\AssessmentAutoSubmitJob',
            'args' => ['answerRecordId' => $answerRecord['id']]
        ];

        $this->getSchedulerService()->register($autoSubmitJob);
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
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->biz->dao('ItemBank:Item:QuestionDao');
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
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }
}
