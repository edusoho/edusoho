<?php

namespace Topxia\Api\Resource;

use Topxia\Api\Util\TagUtil;
use AppBundle\Common\ArrayToolkit;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Course extends BaseResource
{
    public function get(Application $app, Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);
        return $this->filter($course);
    }

    public function filter($course)
    {
        $course = $this->convertOldFields($course);
        $course = $this->filledCourseByCourseSet($course);
        return $course;
    }

    private function convertOldFields($course)
    {
        $course['expiryDay'] = $course['expiryDays'];
        $course['lessonNum'] = $course['taskNum'];
        $course['userId'] = $course['creator'];
        $course['tryLookTime']  = $course['tryLookLength'];
        $course['createdTime'] = date('c', $course['createdTime']);
        return $course;
    }

    private function filledCourseByCourseSet($course)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $copyKeys = array('tags', 'hitNum', 'orgCode', 'orgId',
            'discount', 'categoryId', 'recommended', 'recommendedSeq', 'recommendedTime',
            'subtitle', 'discountId', 'smallPicture', 'middlePicture', 'largePicture'
        );
        if (!empty($courseSet['cover'])) {
            $courseSetImg = array(
                'smallPicture' => $courseSet['cover']['small'],
                'middlePicture' => $courseSet['cover']['middle'],
                'largePicture' => $courseSet['cover']['large']
            );
            $courseSet = array_merge($courseSet, $courseSetImg);
        };

        foreach ($copyKeys as $value) {
            $course[$value] = isset($courseSet[$value]) ? $courseSet[$value] : '';
        }

        $course['tags'] = TagUtil::buildTags('course-set', $courseSet['id']);
        $course['tags'] = ArrayToolkit::column($course['tags'], 'name');

        if ($course['isDefault'] == 1 && $course['title']) {
            $course['title'] = $courseSet['title'];
        }

        return $course;
    }

    public function simplify($res)
    {
        $simple = array();

        $simple['id']      = $res['id'];
        $simple['title']   = $res['title'];
        $simple['picture'] = $this->getFileUrl($res['smallPicture']);
        $simple['convNo']  = $this->getConversation($res['id']);

        return $simple;
    }

    protected function getConversation($courseId)
    {
        $conversation = $this->getConversationService()->getConversationByTarget($courseId, 'course');
        if ($conversation) {
            return $conversation['no'];
        }

        return '';
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM:ConversationService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->getServiceKernel()->createService('Course:CourseSetService');
    }

}
