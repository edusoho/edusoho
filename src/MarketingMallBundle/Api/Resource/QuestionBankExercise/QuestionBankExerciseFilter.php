<?php

namespace MarketingMallBundle\Api\Resource\QuestionBankExercise;

use ApiBundle\Api\Resource\Filter;

class QuestionBankExerciseFilter extends Filter
{
    protected $simpleFields = [
        'id', 'questionBankId', 'title', 'cover', 'originPrice',
    ];

    public function simpleFields(&$data)
    {
        $data['cover'] = [
            'smallPicture' => $data['cover']['small'] ?? '',
            'middlePicture' => $data['cover']['middle'] ?? '',
        ];
    }
}