<?php

namespace Biz\Question\Config;

use Biz\FillQuestion\FillQuestion;
use Biz\EssayQuestion\EssayQuestion;
use Biz\ChoiceQuestion\ChoiceQuestion;
use Codeages\Biz\Framework\Context\Biz;
use Biz\MaterialQuestion\MaterialQuestion;
use Biz\DetermineQuestion\DetermineQuestion;
use Biz\SingleChoiceQuestion\SingleChoiceQuestion;
use Topxia\Common\Exception\InvalidArgumentException;
use Biz\UncertainChoiceQuesiton\UncertainChoiceQuesiton;

class QuestionFactory
{
    /**
     * @param  Biz        $biz
     * @param  $type
     * @return Question
     */
    final public static function create(Biz $biz, $type)
    {
        $questions = self::all($biz);

        $questionType = $questions[$type];
        if (!$questionType) {
            throw new InvalidArgumentException("Question Type Invalid");
        }

        return $questionType;
    }

    final public static function all(Biz $biz)
    {
        return array(
            'single_choice'    => new SingleChoiceQuestion($biz),
            'choice'           => new ChoiceQuestion($biz),
            'uncertain_choice' => new UncertainChoiceQuesiton($biz),
            'fill'             => new FillQuestion($biz),
            'determine'        => new DetermineQuestion($biz),
            'essay'            => new EssayQuestion($biz),
            'material'         => new MaterialQuestion($biz)
        );
    }
}
