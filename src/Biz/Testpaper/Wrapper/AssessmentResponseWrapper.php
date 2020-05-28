<?php

namespace Biz\Testpaper\Wrapper;

use Codeages\Biz\ItemBank\Item\AnswerMode\ChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\SingleChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\TrueFalseAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\UncertainChoiceAnswerMode;

class AssessmentResponseWrapper
{
    public function __construct()
    {
    }

    public function wrap($data, $assessment, $answerRecord)
    {
        $assessmentResponse = [
            'assessment_id' => $assessment['id'],
            'answer_record_id' => $answerRecord['id'],
            'used_time' => empty($data['usedTime']) ? 0 : $data['usedTime'],
            'section_responses' => [],
        ];

        $questionAnswers = empty($data['data']) ? [] : $data['data'];
        foreach ($assessment['sections'] as $section) {
            $sectionResponse = ['section_id' => $section['id'], 'item_responses' => []];
            foreach ($section['items'] as $item) {
                $itemResponse = ['item_id' => $item['id'], 'question_responses' => []];
                foreach ($item['questions'] as $question) {
                    if (!empty($questionAnswers[$question['id']])) {
                        $itemResponse['question_responses'][] = [
                            'question_id' => $question['id'],
                            'response' => $this->convertAnswer($questionAnswers[$question['id']], $question),
                        ];
                    } else {
                        $itemResponse['question_responses'][] = ['question_id' => $question['id'], 'response' => []];
                    }
                }
                $sectionResponse['item_responses'][] = $itemResponse;
            }
            $assessmentResponse['section_responses'][] = $sectionResponse;
        }

        return $assessmentResponse;
    }

    protected function convertAnswer($answers, $question)
    {
        if (in_array($question['answer_mode'], [SingleChoiceAnswerMode::NAME, ChoiceAnswerMode::NAME, UncertainChoiceAnswerMode::NAME])) {
            foreach ($answers as &$answer) {
                if ('' !== $answer) {
                    $answer = chr(65 + intval($answer));
                } else {
                    unset($answer);
                }
            }
        } elseif (TrueFalseAnswerMode::NAME == $question['answer_mode']) {
            foreach ($answers as &$answer) {
                if ('' !== $answer) {
                    $answer = 1 == $answer ? 'T' : 'F';
                } else {
                    unset($answer);
                }
            }
        }

        return $answers;
    }
}
