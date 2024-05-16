<?php

namespace Biz\Question\Traits;

use Codeages\Biz\ItemBank\Item\AnswerMode\ChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\RichTextAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\SingleChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\TextAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\TrueFalseAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\UncertainChoiceAnswerMode;

trait QuestionAnswerModeTrait
{
    protected $modeToType = [
        SingleChoiceAnswerMode::NAME => 'single_choice',
        ChoiceAnswerMode::NAME => 'choice',
        UncertainChoiceAnswerMode::NAME => 'uncertain_choice',
        TrueFalseAnswerMode::NAME => 'determine',
        TextAnswerMode::NAME => 'fill',
        RichTextAnswerMode::NAME => 'essay',
    ];
}
