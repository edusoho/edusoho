<?php

namespace MarketingMallBundle\Api\Resource\MallClassroom;

use ApiBundle\Api\Resource\Filter;

class MallClassroomFilter extends Filter
{
    protected $simpleFields = [
        'id', 'title', 'cover', 'price', 'courseNum', 'smallPicture', 'middlePicture'
    ];

    public function simpleFields(&$data)
    {
        $data['cover'] = [
            'smallPicture' => $data['smallPicture'],
            'middlePicture' => $data['middlePicture'],
        ];
        unset($data['smallPicture'], $data['middlePicture']);
    }
}