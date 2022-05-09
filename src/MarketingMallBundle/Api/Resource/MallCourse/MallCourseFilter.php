<?php

namespace MarketingMallBundle\Api\Resource\MallCourse;

use ApiBundle\Api\Resource\Filter;

class MallCourseFilter extends Filter
{
    protected $simpleFields = [
        'id', 'title', 'courseSetTitle', 'cover', 'price', 'courseSet'
    ];

    public function simpleFields(&$data)
    {
        $courseSet = $data['courseSet'];
        $data['courseSetTitle'] = $courseSet['title'];
        $data['cover'] = [
            'smallPicture' => $courseSet['cover']['small'] ?? '',
            'middlePicture' => $courseSet['cover']['middle'] ?? '',
        ];
        unset($data['courseSet']);
    }
}