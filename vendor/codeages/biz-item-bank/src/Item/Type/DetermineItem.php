<?php

namespace Codeages\Biz\ItemBank\Item\Type;

use Codeages\Biz\ItemBank\Item\AnswerMode\TrueFalseAnswerMode;

class DetermineItem extends Item
{
    const TYPE = 'determine';

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
        $this->allowAnswerModes = [TrueFalseAnswerMode::NAME];
    }

    public function setAllowMaterials()
    {
        $this->allowMaterials = false;
    }
}
