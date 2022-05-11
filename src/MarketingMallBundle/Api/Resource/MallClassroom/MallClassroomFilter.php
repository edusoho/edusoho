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
        foreach ($data as &$classroom) {
            $classroom['cover'] = [
                'smallPicture' => $classroom['smallPicture'],
                'middlePicture' => $classroom['middlePicture'],
            ];
            unset($classroom['smallPicture'], $classroom['middlePicture']);
        }
    }
}