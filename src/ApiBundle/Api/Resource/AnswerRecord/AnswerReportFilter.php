<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\Resource\Filter;

class AnswerReportFilter extends Filter
{
    protected $publicFields = [
        'id',
        'user_id',
        'assessment_id',
        'answer_record_id',
        'answer_scene_id',
        'total_score',
        'score',
        'right_rate',
        'right_question_count',
        'objective_score',
        'subjective_score',
        'grade',
        'comment',
        'review_time',
        'review_user_id',
        'section_reports',
    ];

    protected function publicFields(&$data)
    {
        foreach ($data['section_reports'] as &$sectionReport) {
            foreach ($sectionReport['item_reports'] as &$itemReport) {
                foreach ($itemReport['question_reports'] as &$questionReport) {
                    foreach ($questionReport['response'] as &$response) {
                        $response = $this->convertAbsoluteUrl($response);
                    }
                }
            }
        }
    }
}
