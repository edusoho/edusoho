<?php

namespace MarketingMallBundle\Api\Resource\MallClassroom;

use ApiBundle\Api\Util\AssetHelper;
use MarketingMallBundle\Api\Resource\BaseFilter;

class MallClassroomFilter extends BaseFilter
{
    protected $simpleFields = [
        'id', 'title', 'cover', 'price', 'courseNum', 'smallPicture', 'middlePicture',
    ];

    public function simpleFields(&$data)
    {
        $data['cover'] = [
            'small' => AssetHelper::getFurl($data['smallPicture'], 'classroom.png'),
            'middle' => AssetHelper::getFurl($data['middlePicture'], 'classroom.png'),
        ];
        unset($data['smallPicture'], $data['middlePicture']);
    }
}
