<?php

namespace ApiBundle\Api\Resource\OpenCourse;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class OpenCourseFilter extends Filter
{
    protected $publicFields = array(
        'id', 'title', 'subtitle', 'status','type', 'lessonNum','categoryId','tags',
        'smallPicture','middlePicture','largePicture','about','teacherIds', 'studentNum',
        'hitNum', 'likeNum', 'postNum', 'userId', 'parentId', 'locked', 'recommended',
        'recommendedSeq', 'recommendedTime', 'createdTime','updatedTime', 'orgId', 'orgCode', 'lesson',
    );

    protected function publicFields(&$data)
    {
        $data['smallPicture'] = AssetHelper::getFurl(empty($data['smallPicture']) ? '' : $data['smallPicture'], 'course.png');
        $data['middlePicture'] = AssetHelper::getFurl(empty($data['middlePicture']) ? '' : $data['middlePicture'], 'course.png');
        $data['largePicture'] = AssetHelper::getFurl(empty($data['largePicture']) ? '' : $data['largePicture'], 'course.png');
    }
}