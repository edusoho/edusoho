<?php

namespace Codeages\Biz\ItemBank\Item\Type;

use Codeages\Biz\ItemBank\Item\AnswerMode\ChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\RichTextAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\SingleChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\TextAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\TrueFalseAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\UncertainChoiceAnswerMode;

class MaterialItem extends Item
{
    const TYPE = 'material';

    public function setAllowMinQuestionNum()
    {
        $this->allowMinQuestionNum = 1;
    }

    public function setAllowMaxQuestionNum()
    {
        $this->allowMaxQuestionNum = 50;
    }

    public function setAllowAnswerModes()
    {
        $this->allowAnswerModes = [
            SingleChoiceAnswerMode::NAME,
            UncertainChoiceAnswerMode::NAME,
            ChoiceAnswerMode::NAME,
            TrueFalseAnswerMode::NAME,
            TextAnswerMode::NAME,
            RichTextAnswerMode::NAME,
        ];
    }

    public function setAllowMaterials()
    {
        $this->allowMaterials = true;
    }

    protected function getMaterial($item)
    {
        return $item['material'];
    }
}
