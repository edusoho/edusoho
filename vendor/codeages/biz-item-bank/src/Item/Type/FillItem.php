<?php

namespace Codeages\Biz\ItemBank\Item\Type;

use Codeages\Biz\ItemBank\Item\AnswerMode\TextAnswerMode;

class FillItem extends Item
{
    const TYPE = 'fill';

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
        $this->allowAnswerModes = [TextAnswerMode::NAME];
    }

    public function setAllowMaterials()
    {
        $this->allowMaterials = false;
    }
}
