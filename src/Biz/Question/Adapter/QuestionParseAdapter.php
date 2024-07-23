<?php

namespace Biz\Question\Adapter;

use Codeages\Biz\ItemBank\Item\Constant\ItemDifficulty;
use ExamParser\Constants\QuestionElement;
use ExamParser\Constants\QuestionErrors;

class QuestionParseAdapter
{
    use ChoiceAdapterTrait;
    use TrueOrFalseAdapterTrait;
    use FillAdapterTrait;
    use EssayAdapterTrait;
    use MaterialAdapterTrait;

    public function adapt($parseResult)
    {
        $questions = json_decode($parseResult, true);

        return $this->adaptQuestions($questions);
    }

    private function adaptQuestions($questions)
    {
        $adaptedQuestions = [];
        $adaptMethods = $this->getAdaptMethods();
        foreach ($questions as $question) {
            if (empty($question['type'])) {
                continue;
            }
            $adaptMethod = $adaptMethods[$question['type']];
            $adaptedQuestions[] = $this->$adaptMethod($question);
        }

        return $adaptedQuestions;
    }

    private function adaptQuestion($question)
    {
        $adaptedQuestion = [
            'stem' => $this->adaptStem($question['title'] ?? ''),
            'difficulty' => $this->adaptDifficulty($question),
            'score' => $question['score'] ?? 2,
            'analysis' => $question['analysis'] ?? '',
        ];
        $errors = $this->adaptCommonErrors($question);
        if ($errors) {
            $adaptedQuestion['errors'] = $errors;
        }

        return $adaptedQuestion;
    }

    private function adaptDifficulty($question)
    {
        if (empty($question['difficulty'])) {
            return ItemDifficulty::NORMAL;
        }
        $difficulties = [
            '简单' => ItemDifficulty::SIMPLE,
            '一般' => ItemDifficulty::NORMAL,
            '困难' => ItemDifficulty::DIFFICULTY,
        ];

        return $difficulties[$question['difficulty']] ?? ItemDifficulty::NORMAL;
    }

    private function adaptStem($stem)
    {
        preg_match_all('/^<p>.*/', $stem, $match);
        if (empty($match[0])) {
            $stem = "<p>{$stem}</p>";
        }

        return $stem;
    }

    private function adaptCommonErrors($question)
    {
        if (empty($question['errors'])) {
            return [];
        }
        $errors = [];
        foreach ($question['errors'] as $error) {
            if ('TITLE_MISSING' == $error['code']) {
                $errors[QuestionElement::STEM] = $this->adaptError(QuestionElement::STEM, QuestionErrors::NO_STEM);
            }
        }

        return $errors;
    }

    private function adaptError($element, $code, $index = -1)
    {
        return [
            'element' => $element,
            'index' => $index,
            'code' => $code,
            'message' => QuestionErrors::getErrorMsg($code),
        ];
    }

    private function getAdaptMethods()
    {
        return [
            'choice' => 'adaptChoice',
            'multipleChoice' => 'adaptMultipleChoice',
            'uncertainChoice' => 'adaptUncertainChoice',
            'trueOrFalse' => 'adaptTrueOrFalse',
            'fill' => 'adaptFill',
            'essay' => 'adaptEssay',
            'material' => 'adaptMaterial',
        ];
    }
}
