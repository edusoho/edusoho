<?php

namespace Codeages\Biz\ItemBank\Assessment\Util;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class AssessmentReview
{
    protected $biz;

    protected $assessment;

    protected $sections;

    protected $items;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function setAssessment($assessment)
    {
        $this->assessment = $assessment;

        $this->sections = ArrayToolkit::index($this->getSectionService()->findSectionsByAssessmentId($assessment['id']), 'id');

        $this->items = ArrayToolkit::index($this->getSectionItemService()->findSectionItemsByAssessmentId($assessment['id']), 'item_id');
    }

    public function review($assessment, $sectionResponses)
    {
        $this->setAssessment($assessment);
        $report = [
            'id' => $this->assessment['id'],
            'total_score' => $this->assessment['total_score'],
            'score' => 0,
            'section_reports' => [],
        ];
        foreach ($sectionResponses as $sectionResponse) {
            $sectionReport = $this->reviewSection($sectionResponse);

            $report['score'] += $sectionReport['score'];
            $report['section_reports'][] = $sectionReport;
        }

        return $report;
    }

    protected function reviewSection($sectionResponse)
    {
        $report = $this->getDefaultSectionReport($sectionResponse);
        if (empty($this->sections[$sectionResponse['section_id']])) {
            return $report;
        }

        $section = $this->sections[$sectionResponse['section_id']];
        $itemReviewResults = $this->getItemService()->review($sectionResponse['item_responses']);
        foreach ($itemReviewResults as $itemReviewResult) {
            $itemReport = $this->reviewItem($itemReviewResult);

            $report['score'] += $itemReport['score'];
            $report['item_reports'][] = $itemReport;
        }
        $report['total_score'] = $section['total_score'];

        return $report;
    }

    protected function reviewItem($itemReviewResult)
    {
        $report = $this->getDefaultItemReport($itemReviewResult);
        if (empty($this->items[$itemReviewResult['item_id']])) {
            return $report;
        }

        $item = $this->items[$itemReviewResult['item_id']];
        foreach ($itemReviewResult['question_responses_review_result'] as $questionReviewResult) {
            $questionReport = $this->reviewQuestion(
                $questionReviewResult,
                $item['question_scores'],
                $item['score_rule']
            );

            $report['question_reports'][] = $questionReport;
            ++$report['question_count'];
            $report['score'] += $questionReport['score'];
        }
        $report['total_score'] = $item['score'];

        return $report;
    }

    protected function reviewQuestion($questionResult, $scores, $rules)
    {
        $report = $this->getDefaultQuestionReport($questionResult);
        if (empty($scores) || empty($rules)) {
            return $report;
        }

        $scores = ArrayToolkit::index($scores, 'question_id');
        $rules = ArrayToolkit::index($rules, 'question_id');
        if (empty($scores[$questionResult['question_id']]) || empty($rules[$questionResult['question_id']])) {
            return $report;
        }

        $result = $this->getScoreRuleProcessor()->review($questionResult, $rules[$questionResult['question_id']]['rule']);
        $report['total_score'] = $scores[$questionResult['question_id']]['score'];
        $report['score'] = $result['score'];
        $report['status'] = $result['status'];

        return $report;
    }

    protected function getDefaultSectionReport($sectionResult)
    {
        return [
            'id' => $sectionResult['section_id'],
            'total_score' => 0,
            'score' => 0,
            'item_reports' => [],
        ];
    }

    protected function getDefaultItemReport($itemResult)
    {
        return [
            'id' => $itemResult['item_id'],
            'total_score' => 0,
            'score' => 0,
            'question_count' => 0,
            'question_reports' => [],
        ];
    }

    protected function getDefaultQuestionReport($questionResult)
    {
        return [
            'id' => $questionResult['question_id'],
            'total_score' => 0,
            'score' => 0,
            'status' => '',
            'response' => $questionResult['response'],
        ];
    }

    protected function getScoreRuleProcessor()
    {
        return $this->biz['score_rule_processor'];
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->biz->service('ItemBank:Item:ItemService');
    }

    /**
     * @return AssessmentSectionItemService
     */
    protected function getSectionItemService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentSectionItemService');
    }

    /**
     * @return AssessmentSectionService
     */
    protected function getSectionService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentSectionService');
    }
}
