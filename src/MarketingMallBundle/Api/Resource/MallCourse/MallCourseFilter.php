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
        foreach ($data as &$course) {
            $courseSet = $course['courseSet'];
            $course['courseSetTitle'] = $courseSet['title'];
            $course['cover'] = [
                'smallPicture' => $courseSet['cover']['small'] ?? '',
                'middlePicture' => $courseSet['cover']['middle'] ?? '',
            ];
            unset($course['courseSet']);
        }
    }
}