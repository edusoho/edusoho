<?php

namespace Codeages\Biz\ItemBank\Answer\Service\Impl;

use Codeages\Biz\ItemBank\BaseService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerReportException;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerException;
use Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Item\Service\AttachmentService;

class AnswerReportServiceImpl extends BaseService implements AnswerReportService
{
    public function create(array $answerReport)
    {
        $answerReport = $this->getValidator()->validate($answerReport, [
            'user_id' => ['required', 'integer', ['min', 0]],
            'assessment_id' => ['required', 'integer', ['min', 0]],
            'answer_record_id' => ['required', 'integer', ['min', 0]],
            'score' => ['numeric', ['min', 0]],
            'total_score' => ['numeric', ['min', 0]],
            'right_rate' => ['numeric', ['min', 0]],
            'right_question_count' => ['integer'],
            'objective_score' => ['numeric', ['min', 0]],
            'subjective_score' => ['numeric', ['min', 0]],
            'grade' => [],
            'review_time' => ['integer'],
            'comment' => [],
        ]);

        if (empty($this->getAssessmentService()->getAssessment($answerReport['assessment_id']))) {
            throw new AssessmentException('Assessment not found.', ErrorCode::ASSESSMENT_NOTFOUND);
        }

        $answerRecord = $this->getAnswerRecordService()->get($answerReport['answer_record_id']);
        if (empty($answerRecord)) {
            throw new AnswerException('Answer record not found.', ErrorCode::ANSWER_RECORD_NOTFOUND);
        }
        $answerReport['answer_scene_id'] = $answerRecord['answer_scene_id'];

        return $this->getAnswerReportDao()->create($answerReport);
    }

    public function update($id, array $answerReport)
    {
        $answerReport = $this->getValidator()->validate($answerReport, [
            'score' => ['numeric', ['min', 0]],
            'right_rate' => ['numeric', ['min', 0]],
            'review_user_id' => ['integer'],
            'review_time' => ['integer'],
            'right_question_count' => ['integer'],
            'objective_score' => ['numeric', ['min', 0]],
            'subjective_score' => ['numeric', ['min', 0]],
            'grade' => [],
            'comment' => [],
        ]);

        if (empty($this->getSimple($id))) {
            throw new AnswerReportException('Answer report not found.', ErrorCode::ANSWER_REPORT_NOTFOUND);
        }

        if (isset($answerReport['grade']) && empty($answerReport['grade'])) {
            unset($answerReport['grade']);
        }

        return $this->getAnswerReportDao()->update($id, $answerReport);
    }

    public function get($id)
    {
        $simpleAnswerReport = $answerReport = $this->getSimple($id);
        if (empty($simpleAnswerReport)) {
            return [];
        }

        $answerQuestionReports = $this->wrapperAnswerQuestionReports(
            $this->getAnswerQuestionReportService()->findByAnswerRecordId($simpleAnswerReport['answer_record_id']), 
            $simpleAnswerReport['assessment_id']
        );
        $attachments = $this->getAttachmentService()->findAttachmentsByTargetIdsAndTargetType(
            ArrayToolkit::column($answerQuestionReports, 'id'),
            AttachmentService::ANSWER_TYPE
        );
        $assessmentSections = ArrayToolkit::index($this->geAssessmentSectionService()->findSectionsByAssessmentId($simpleAnswerReport['assessment_id']), 'id');
        $sectionReports = ArrayToolkit::group($answerQuestionReports, 'section_id');
        $attachments = ArrayToolkit::group($attachments, 'target_id');
        foreach ($sectionReports as $sectionId => &$sectionReport) {
            $itemReports = ArrayToolkit::group($sectionReport, 'item_id');
            foreach ($itemReports as $itemId => &$itemReport) {
                $questionReports = [];
                foreach ($itemReport as $questionReport) {
                    $questionReport['attachments'] = empty($attachments[$questionReport['id']]) ? [] : $attachments[$questionReport['id']];
                    $questionReports[] = ArrayToolkit::parts($questionReport, ['id', 'question_id', 'total_score', 'score', 'comment', 'status', 'response', 'attachments']);
                }
                $itemReport = [
                    'item_id' => $itemId,
                    'total_score' => array_sum(ArrayToolkit::column($itemReport, 'total_score')),
                    'score' => array_sum(ArrayToolkit::column($itemReport, 'score')),
                    'question_count' => count($itemReport),
                    'question_reports' => $questionReports,
                ];
            }

            $sectionReportGroupByStatus = ArrayToolkit::group($sectionReport, 'status');
            $sectionReport = [
                'section_id' => $sectionId,
                'section_name' => empty($assessmentSections[$sectionId]['name']) ? '' : $assessmentSections[$sectionId]['name'],
                'total_score' => array_sum(ArrayToolkit::column($sectionReport, 'total_score')),
                'score' => array_sum(ArrayToolkit::column($sectionReport, 'score')),
                'question_count' => count($sectionReport),
                'right_question_num' => empty($sectionReportGroupByStatus[AnswerQuestionReportService::STATUS_RIGHT]) ? 0 : count($sectionReportGroupByStatus[AnswerQuestionReportService::STATUS_RIGHT]),
                'wrong_question_num' => empty($sectionReportGroupByStatus[AnswerQuestionReportService::STATUS_WRONG]) ? 0 : count($sectionReportGroupByStatus[AnswerQuestionReportService::STATUS_WRONG]),
                'reviewing_question_num' => empty($sectionReportGroupByStatus[AnswerQuestionReportService::STATUS_REVIEWING]) ? 0 : count($sectionReportGroupByStatus[AnswerQuestionReportService::STATUS_REVIEWING]),
                'no_answer_question_num' => empty($sectionReportGroupByStatus[AnswerQuestionReportService::STATUS_NOANSWER]) ? 0 : count($sectionReportGroupByStatus[AnswerQuestionReportService::STATUS_NOANSWER]),
                'part_right_question_num' => empty($sectionReportGroupByStatus[AnswerQuestionReportService::STATUS_PART_RIGHT]) ? 0 : count($sectionReportGroupByStatus[AnswerQuestionReportService::STATUS_PART_RIGHT]),
                'item_reports' => array_values($itemReports),
            ];
        }

        $answerReport['section_reports'] = array_values($sectionReports);

        return $answerReport;
    }

    protected function wrapperAnswerQuestionReports($answerQuestionReports, $assessmentId)
    {
        $assessmentQuestions = $this->getAssessmentService()->findAssessmentQuestions($assessmentId);
        foreach ($answerQuestionReports as &$questionReport) {
            if (!empty($assessmentQuestions[$questionReport['question_id']])) {
                $questionReport['total_score'] = $assessmentQuestions[$questionReport['question_id']]['score'];
                $questionReport['section_id'] = $assessmentQuestions[$questionReport['question_id']]['section_id'];
                $questionReport['seq'] = $assessmentQuestions[$questionReport['question_id']]['seq'];
            }
        }
        return $this->sortPerArrayValue($answerQuestionReports, 'seq');
    }

    protected function sortPerArrayValue($arr, $attrName, $ascending = true)
    {
        usort(
            $arr,
            function ($first, $next) use ($ascending, $attrName) {
                if ($ascending) {
                    return $first[$attrName] > $next[$attrName] ? 1 : -1;
                } else {
                    return $first[$attrName] < $next[$attrName] ? 1 : -1;
                }
            }
        );
    
        return $arr;
    }

    public function getSimple($id)
    {
        return $this->getAnswerReportDao()->get($id);
    }

    public function findByAnswerSceneId($answerSceneId)
    {
        return $this->getAnswerReportDao()->findByAnswerSceneId($answerSceneId);
    }

    public function search($conditions, $orderBys, $start, $limit, $columns = array())
    {
        return $this->getAnswerReportDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function count($conditions)
    {
        return $this->getAnswerReportDao()->count($conditions);
    }

    public function findByIds(array $ids)
    {
        return $this->getAnswerReportDao()->findByIds($ids);
    }

    /**
     * @return \Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionService
     */
    protected function geAssessmentSectionService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentSectionService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Assessment\Service\AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerQuestionReportService');
    }

    protected function getAnswerReportDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerReportDao');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Item\Service\AttachmentService
     */
    protected function getAttachmentService()
    {
        return $this->biz->service('ItemBank:Item:AttachmentService');
    }
}
