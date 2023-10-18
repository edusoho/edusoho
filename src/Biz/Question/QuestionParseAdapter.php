<?php

namespace Biz\Question;

use Codeages\Biz\ItemBank\Item\Constant\ItemDifficulty;
use Codeages\Biz\ItemBank\Item\Type\ChoiceItem;
use Codeages\Biz\ItemBank\Item\Type\DetermineItem;
use Codeages\Biz\ItemBank\Item\Type\EssayItem;
use Codeages\Biz\ItemBank\Item\Type\FillItem;
use Codeages\Biz\ItemBank\Item\Type\MaterialItem;
use Codeages\Biz\ItemBank\Item\Type\SingleChoiceItem;
use Codeages\Biz\ItemBank\Item\Type\UncertainChoiceItem;

class QuestionParseAdapter
{
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
            $adaptMethod = $adaptMethods[$question['type']];
            $adaptedQuestions[] = $this->$adaptMethod($question);
        }

        return $adaptedQuestions;
    }

    private function adaptQuestion($question)
    {
        return [
            'stem' => $question['title'],
            'difficulty' => $this->adaptDifficulty($question),
            'score' => $question['score'] ?? 2,
            'analysis' => $question['analysis'],
            'errors' => $this->adaptErrors($question),
        ];
    }

    private function adaptChoice($question)
    {
        $adaptQuestion = $this->adaptQuestion($question);
        $adaptQuestion['type'] = SingleChoiceItem::TYPE;
        $adaptQuestion['options'] = array_column($question['body']['options'], 'content');
        $adaptQuestion['answers'] = $this->adaptChoiceAnswer($question['answer']['correct']);

        return $adaptQuestion;
    }

    private function adaptMultipleChoice($question)
    {
        $question = $this->adaptChoice($question);
        $question['type'] = ChoiceItem::TYPE;

        return $question;
    }

    private function adaptUncertainChoice($question)
    {
        $question = $this->adaptChoice($question);
        $question['type'] = UncertainChoiceItem::TYPE;

        return $question;
    }

    private function adaptTrueOrFalse($question)
    {
        $adaptQuestion = $this->adaptQuestion($question);
        $adaptQuestion['type'] = DetermineItem::TYPE;
        $adaptQuestion['answer'] = $this->adaptTrueOrFalseAnswer($question['answer']['correct'][0]);

        return $adaptQuestion;
    }

    private function adaptFill($question)
    {
        $adaptQuestion = $this->adaptQuestion($question);
        $adaptQuestion['type'] = FillItem::TYPE;
        $adaptQuestion['answers'] = $this->adaptFillAnswer($question['answer']['correct']);

        return $adaptQuestion;
    }

    private function adaptEssay($question)
    {
        $adaptQuestion = $this->adaptQuestion($question);
        $adaptQuestion['type'] = EssayItem::TYPE;
        $adaptQuestion['answer'] = $question['answer']['correct'][0];

        return $adaptQuestion;
    }

    private function adaptMaterial($question)
    {
        $adaptQuestion = $this->adaptQuestion($question);
        $adaptQuestion['type'] = MaterialItem::TYPE;
        $adaptQuestion['subQuestions'] = $this->adaptQuestions($question['children']);

        return $adaptQuestion;
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

        return $difficulties[$question['difficulty']] ?? '';
    }

    private function adaptChoiceAnswer($answers)
    {
        return array_map(function ($answer) {
            return $answer - 1;
        }, $answers);
    }

    private function adaptTrueOrFalseAnswer($answer)
    {
        $answers = [
            '1' => true,
            '2' => false,
        ];

        return $answers[$answer];
    }

    private function adaptFillAnswer($answers)
    {
        return array_map(function ($answer) {
            return implode('|', array_column($answer, 'content'));
        }, $answers);
    }

    private function adaptErrors($question)
    {
        if (empty($question['errors'])) {
            return [];
        }

        return $question['errors'];
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
