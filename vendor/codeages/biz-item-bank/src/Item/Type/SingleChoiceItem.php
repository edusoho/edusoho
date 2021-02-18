<?php

namespace Codeages\Biz\ItemBank\Item\Type;

use Codeages\Biz\ItemBank\Item\AnswerMode\SingleChoiceAnswerMode;

class SingleChoiceItem extends Item
{
    const TYPE = 'single_choice';

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
        $this->allowAnswerModes = [SingleChoiceAnswerMode::NAME];
    }

    public function setAllowMaterials()
    {
        $this->allowMaterials = false;
    }
}
