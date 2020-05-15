<?php

namespace Codeages\Biz\ItemBank\Item\Type;

use Codeages\Biz\ItemBank\Item\AnswerMode\RichTextAnswerMode;

class EssayItem extends Item
{
    const TYPE = 'essay';

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
        $this->allowAnswerModes = [RichTextAnswerMode::NAME];
    }

    public function setAllowMaterials()
    {
        $this->allowMaterials = false;
    }
}
