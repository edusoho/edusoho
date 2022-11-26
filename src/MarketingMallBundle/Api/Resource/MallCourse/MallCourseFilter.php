<?php

namespace MarketingMallBundle\Api\Resource\MallCourse;

use MarketingMallBundle\Api\Resource\BaseFilter;

class MallCourseFilter extends BaseFilter
{
    protected $simpleFields = [
        'id', 'title', 'courseSetTitle', 'cover', 'price', 'courseSet',
    ];

    public function simpleFields(&$course)
    {
        $courseSet = $course['courseSet'] ?? [];
        $course['courseSetTitle'] = $courseSet['title'] ?? '';
        $course['cover'] = [
            'small' => $courseSet['cover']['small'] ?? '',
            'middle' => $courseSet['cover']['middle'] ?? '',
        ];
        unset($course['courseSet']);

        $course['cover'] = $this->transformCover($course['cover']);
    }
}
