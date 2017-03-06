<?php

namespace Topxia\Api\Resource;

use Topxia\Api\Util\TagUtil;
use AppBundle\Common\ArrayToolkit;

class CourseBaseResource extends BaseResource
{
    public function filter($res)
    {
        return $res;
    }

    protected function convertOldFields($course)
    {
        $course['expiryDay'] = $course['expiryDays'];
        $course['lessonNum'] = $course['taskNum'];
        $course['userId'] = $course['creator'];
        $course['tryLookTime']  = $course['tryLookLength'];
        return $course;
    }

    protected function filledCourseByCourseSet($course, $courseSet)
    {
        $copyKeys = array('tags', 'hitNum', 'orgCode', 'orgId',
            'discount', 'categoryId', 'recommended', 'recommendedSeq', 'recommendedTime',
            'subtitle', 'discountId', 'smallPicture', 'middlePicture', 'largePicture'
        );
        if (empty($courseSet['cover'])) {
            $courseSetImg = array(
                'smallPicture' => '',
                'middlePicture' => '',
                'largePicture' => ''
            );
        } else {
            $courseSetImg = array(
                'smallPicture' => $this->getFileUrl($courseSet['cover']['small']),
                'middlePicture' => $this->getFileUrl($courseSet['cover']['middle']),
                'largePicture' => $this->getFileUrl($courseSet['cover']['large'])
            );
        };

        $courseSet = array_merge($courseSet, $courseSetImg);
        foreach ($copyKeys as $value) {
            $course[$value] = $courseSet[$value];
        }

        $course['tags'] = TagUtil::buildTags('course-set', $courseSet['id']);
        $course['tags'] = ArrayToolkit::column($course['tags'], 'name');
        return $course;
    }
}
