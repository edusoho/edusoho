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
        $assessmentResponse = array(
            'assessment_id' => $assessment['id'],
            'answer_record_id' => $answerRecord['id'],
            'used_time' => empty($data['usedTime']) ? 0 : $data['usedTime'],
            'section_responses' => array(),
        );

        $questionAnswers = $data['data'];
        foreach ($assessment['sections'] as $section) {
            $sectionResponse = array('section_id' => $section['id'], 'item_responses' => array());
            foreach ($section['items'] as $item) {
                $itemResponse = array('item_id' => $item['id'], 'question_responses' => array());
                foreach ($item['questions'] as $question) {
                    if (!empty($questionAnswers[$question['id']])) {
                        $itemResponse['question_responses'][] = array(
                            'question_id' => $question['id'],
                            'response' => $this->convertAnswer($questionAnswers[$question['id']], $question),
                        );
                    } else {
                        $itemResponse['question_responses'][] = array('question_id' => $question['id'], 'response' => array());
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
        if (in_array($question['answer_mode'], array(SingleChoiceAnswerMode::NAME, ChoiceAnswerMode::NAME, UncertainChoiceAnswerMode::NAME))) {
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
