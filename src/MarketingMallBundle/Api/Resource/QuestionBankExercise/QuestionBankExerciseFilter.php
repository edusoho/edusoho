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
        foreach ($data as &$bank) {
            $bank['cover'] = [
                'smallPicture' => $bank['cover']['small'] ?? '',
                'middlePicture' => $bank['cover']['middle'] ?? '',
            ];
        }
    }
}