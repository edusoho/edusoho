<?php

namespace MarketingMallBundle\Api\Resource\QuestionBankExercise;

use MarketingMallBundle\Api\Resource\BaseFilter;

class QuestionBankExerciseFilter extends BaseFilter
{
    protected $simpleFields = [
        'id', 'questionBankId', 'title', 'cover', 'originPrice',
    ];

    public function simpleFields(&$bank)
    {
        $bank['cover'] = $this->transformCover($bank['cover'], 'item_bank_exercise.png');
    }
}
