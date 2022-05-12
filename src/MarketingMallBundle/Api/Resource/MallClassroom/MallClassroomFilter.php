<?php

namespace MarketingMallBundle\Api\Resource\MallClassroom;

use MarketingMallBundle\Api\Resource\BaseFilter;

class MallClassroomFilter extends BaseFilter
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