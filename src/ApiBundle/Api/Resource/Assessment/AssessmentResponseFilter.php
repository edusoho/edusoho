<?php

namespace ApiBundle\Api\Resource\Assessment;

use ApiBundle\Api\Resource\Filter;

class AssessmentResponseFilter extends Filter
{
    protected $publicFields = array(
        'assessment_id',
        'answer_record_id',
        'used_time',
        'section_responses',
    );

    protected function publicFields(&$assessmentResponse)
    {
        foreach ($assessmentResponse['section_responses'] as &$section) {
            foreach ($section['item_responses'] as &$item) {
                foreach ($item['question_responses'] as &$question) {
                    if (is_array($question['response'])) {
                        foreach ($question['response'] as &$point) {
                            $point = $this->convertAbsoluteUrl($point);
                        }
                    }
                }
            }
        }
    }
}
