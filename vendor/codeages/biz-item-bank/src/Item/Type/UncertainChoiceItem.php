<?php

namespace Codeages\Biz\ItemBank\Item\Type;

use Codeages\Biz\ItemBank\Item\AnswerMode\UncertainChoiceAnswerMode;

class UncertainChoiceItem extends Item
{
    const TYPE = 'uncertain_choice';

    public function setAllowMinQuestionNum()
    {
        $this->allowMinQuestionNum = 1;
    }

    public function setAllowMaxQuestionNum()
    {
        $this->allowMaxQuestionNum = 1;
    }

    public function setAllowAnswerModes()
    {
        $this->allowAnswerModes = [UncertainChoiceAnswerMode::NAME];
    }

    public function setAllowMaterials()
    {
        $this->allowMaterials = false;
    }
}
