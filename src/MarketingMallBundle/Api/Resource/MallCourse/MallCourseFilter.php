<?php

namespace MarketingMallBundle\Api\Resource\MallCourse;

use MarketingMallBundle\Api\Resource\BaseFilter;

class MallCourseFilter extends BaseFilter
{
    protected $simpleFields = [
        'id', 'title', 'courseSetTitle', 'cover', 'price', 'courseSet'
    ];

    public function simpleFields(&$course)
    {
        $courseSet = $course['courseSet'] ?? [];
        $course['courseSetTitle'] = $courseSet['title'] ?? '';
        $course['cover'] = [
            'smallPicture' => $courseSet['cover']['small'] ?? '',
            'middlePicture' => $courseSet['cover']['middle'] ?? '',
        ];
        unset($course['courseSet']);
    }
}