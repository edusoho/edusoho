<?php

namespace Biz\Testpaper\Wrapper;

use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\ItemBank\Item\AnswerMode\ChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\RichTextAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\SingleChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\TextAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\TrueFalseAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\UncertainChoiceAnswerMode;

class TestpaperWrapper
{
    protected $modeToType = array(
        SingleChoiceAnswerMode::NAME => 'single_choice',
        ChoiceAnswerMode::NAME => 'choice',
        UncertainChoiceAnswerMode::NAME => 'uncertain_choice',
        TrueFalseAnswerMode::NAME => 'determine',
        TextAnswerMode::NAME => 'fill',
        RichTextAnswerMode::NAME => 'essay',
    );

    protected $answerStatus = array(
        'right' => 'right',
        'wrong' => 'wrong',
        'no_answer' => 'noAnswer',
        'part_right' => 'partRight',
        'reviewing' => 'none',
    );

    protected $questionReports = array();

    public function __construct()
    {
    }

    public function wrapTestpaper($assessment, $scene = array())
    {
        $testpaper = array(
            'id' => $assessment['id'],
            'name' => $assessment['name'],
            'description' => $assessment['description'],
            'bankId' => $assessment['bank_id'],
            'limitedTime' => empty($scene['limited_time']) ? '0' : $scene['limited_time'],
            'score' => $assessment['total_score'],
            'itemCount' => $assessment['item_count'],
            'createdUserId' => $assessment['created_user_id'],
            'createdTime' => $assessment['created_time'],
            'updatedUserId' => $assessment['updated_user_id'],
            'updatedTime' => $assessment['updated_time'],
            'metas' => array(
                'counts' => array(
                    'single_choice' => '0',
                    'choice' => '0',
                    'essay' => '0',
                    'uncertain_choice' => '0',
                    'determine' => '0',
                    'fill' => '0',
                    'material' => '0',
                ),
                'scores' => array(
                    'single_choice' => '0',
                    'choice' => '0',
                    'essay' => '0',
                    'uncertain_choice' => '0',
                    'determine' => '0',
                    'fill' => '0',
                    'material' => '0',
                ),
                'totalScore' => $assessment['total_score'],
            ),
        );
        foreach ($assessment['sections'] as $section) {
            foreach ($section['items'] as $item) {
                if (1 != $item['isDelete']) {
                    ++$testpaper['metas']['counts'][$item['type']];
                    $testpaper['metas']['scores'][$item['type']] += $item['score'];
                }
            }
        }

        return $testpaper;
    }

    public function wrapTestpaperResult($record, $assessment, $scene, $report = array())
    {
        if (empty($record)) {
            return array();
        }

        return array(
            'id' => $record['id'],
            'paperName' => $assessment['name'],
            'testId' => $assessment['id'],
            'userId' => $record['user_id'],
            'score' => empty($report['score']) ? '0' : $report['score'],
            'objectiveScore' => empty($report['objective_score']) ? '0' : $report['objective_score'],
            'subjectiveScore' => empty($report['subjective_score']) ? '0' : $report['subjective_score'],
            'teacherSay' => empty($report['comment']) ? '' : $report['comment'],
            'rightItemCount' => empty($report['right_rate']) ? '0' : $report['right_rate'],
            'passedStatus' => empty($report['grade']) ? 'none' : $report['grade'],
            'limitedTime' => $scene['limited_time'],
            'beginTime' => $record['begin_time'],
            'endTime' => $record['end_time'],
            'updateTime' => $record['updated_time'],
            'metas' => array(),
            'status' => $record['status'],
            'checkTeacherId' => empty($report['review_user_id']) ? '0' : $report['review_user_id'],
            'checkedTime' => empty($report['review_time']) ? '0' : $report['review_time'],
            'usedTime' => $record['used_time'],
        );
    }

    public function wrapTestpaperItems($assessment, $questionReports = array())
    {
        $items = array();
        $this->questionReports = ArrayToolkit::index($questionReports, 'question_id');

        foreach ($assessment['sections'] as $section) {
            foreach ($section['items'] as $item) {
                if (1 != $item['isDelete']) {
                    $items[$item['id']] = $this->wrapItem($item);
                }
            }
        }

        return $items;
    }

    protected function wrapItem($item)
    {
        if ('material' == $item['type']) {
            $question = array(
                'id' => $item['id'],
                'type' => 'material',
                'questionType' => 'material',
                'stem' => $item['material'],
                'score' => empty($item['score']) ? '0' : strval($item['score']),
                'metas' => array(),
                'difficulty' => $item['difficulty'],
                'subCount' => strval(count($item['questions'])),
                'seq' => strval($item['seq']),
                'categoryId' => $item['category_id'],
                'analysis' => $item['analysis'],
                'parentId' => '0',
                'subs' => array(),
            );
            foreach ($item['questions'] as $itemQuestion) {
                if (1 != $itemQuestion['isDelete']) {
                    $question['subs'][$itemQuestion['id']] = $this->wrapQuestion($item, $itemQuestion);
                }
            }
        } else {
            $question = $this->wrapQuestion($item, reset($item['questions']));
        }

        return $question;
    }

    protected function wrapQuestion($item, $itemQuestion)
    {
        $question = array(
            'id' => $itemQuestion['id'],
            'type' => $this->modeToType[$itemQuestion['answer_mode']],
            'questionType' => $this->modeToType[$itemQuestion['answer_mode']],
            'stem' => $itemQuestion['stem'],
            'score' => strval($itemQuestion['score']),
            'metas' => $this->convertMetas($itemQuestion),
            'difficulty' => $item['difficulty'],
            'subCount' => '0',
            'missScore' => empty($itemQuestion['miss_score']) ? '0' : $itemQuestion['miss_score'],
            'seq' => empty($itemQuestion['seq']) ? '0' : strval($itemQuestion['seq']),
            'categoryId' => $item['category_id'],
            'analysis' => $itemQuestion['analysis'],
            'parentId' => '0',
            'testResult' => array(),
        );

        $question['answer'] = $this->convertAnswer($itemQuestion['answer'], $question);

        if ('material' == $item['type']) {
            $question['parentId'] = $item['id'];
        }

        if ('fill' == $question['type']) {
            $question['stem'] = $this->fillStemAnswer($question['stem'], $question['answer']);
        }

        if (!empty($this->questionReports[$question['id']])) {
            $questionReport = $this->questionReports[$question['id']];
            $question['testResult'] = array(
                'id' => $questionReport['id'],
                'testId' => $questionReport['assessment_id'],
                'resultId' => $questionReport['answer_record_id'],
                'questionId' => $questionReport['question_id'],
                'status' => $this->answerStatus[$questionReport['status']],
                'score' => $questionReport['score'],
                'answer' => $this->convertAnswer($questionReport['response'], $question),
                'teacherSay' => $questionReport['comment'],
            );
        }

        return $question;
    }

    protected function fillStemAnswer($stem, $answers)
    {
        foreach ($answers as $answer) {
            $stem = preg_replace('/(\[\[]])/is', '[['.$answer.']]', $stem, 1);
        }

        return $stem;
    }

    protected function convertAnswer($answer, $question)
    {
        if (in_array($question['type'], array('uncertain_choice', 'single_choice', 'choice'))) {
            foreach ($answer as &$answerItem) {
                if ('' !== $answerItem) {
                    $answerItem = (string) (ord($answerItem) - 65);
                } else {
                    unset($answerItem);
                }
            }
        } elseif ('determine' == $question['type']) {
            foreach ($answer as &$answerItem) {
                if ('' !== $answerItem) {
                    $answerItem = 'T' == $answerItem ? '1' : '0';
                } else {
                    unset($answerItem);
                }
            }
        }

        return $answer;
    }

    protected function convertMetas($question)
    {
        $metas = array();
        if (in_array($this->modeToType[$question['answer_mode']], array('uncertain_choice', 'single_choice', 'choice'))) {
            foreach ($question['response_points'] as $points) {
                $point = array_shift($points);
                if (!empty($point['text'])) {
                    $metas['choices'][] = $point['text'];
                }
            }
        }

        return $metas;
    }
}
