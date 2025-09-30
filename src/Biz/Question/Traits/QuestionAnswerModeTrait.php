<?php

namespace Biz\Question\Traits;

use Codeages\Biz\ItemBank\Item\AnswerMode\ChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\RichTextAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\SingleChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\TextAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\TrueFalseAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\UncertainChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\Type\ChoiceItem;
use Codeages\Biz\ItemBank\Item\Type\DetermineItem;
use Codeages\Biz\ItemBank\Item\Type\EssayItem;
use Codeages\Biz\ItemBank\Item\Type\FillItem;
use Codeages\Biz\ItemBank\Item\Type\SingleChoiceItem;
use Codeages\Biz\ItemBank\Item\Type\UncertainChoiceItem;

trait QuestionAnswerModeTrait
{
    protected $modeToType = [
        SingleChoiceAnswerMode::NAME => SingleChoiceItem::TYPE,
        ChoiceAnswerMode::NAME => ChoiceItem::TYPE,
        UncertainChoiceAnswerMode::NAME => UncertainChoiceItem::TYPE,
        TrueFalseAnswerMode::NAME => DetermineItem::TYPE,
        TextAnswerMode::NAME => FillItem::TYPE,
        RichTextAnswerMode::NAME => EssayItem::TYPE,
    ];
}
