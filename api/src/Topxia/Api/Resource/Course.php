<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Api\Util\TagUtil;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Biz\Course\Util\CourseTitleUtils;

class Course extends BaseResource
{
    public function get(Application $app, Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);

        $course['courseSet'] = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

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
        $course['tryLookTime'] = $course['tryLookLength'];
        $course['createdTime'] = date('c', $course['createdTime']);

        $enableAudioStatus = $this->getCourseService()->isSupportEnableAudio($course['enableAudio']);
        $course['isAudioOn'] = $enableAudioStatus ? 1 : 0;
        unset($course['enableAudio']);

        return $course;
    }

    private function filledCourseByCourseSet($course)
    {
        $courseSet = $course['courseSet'];
        $copyKeys = array('tags', 'hitNum', 'orgCode', 'orgId',
            'discount', 'categoryId', 'recommended', 'recommendedSeq', 'recommendedTime',
            'subtitle', 'discountId', 'smallPicture', 'middlePicture', 'largePicture',
        );

        $smallPicture = empty($courseSet['cover']['small']) ? '' : $courseSet['cover']['small'];
        $middlePicture = empty($courseSet['cover']['middle']) ? '' : $courseSet['cover']['middle'];
        $largePicture = empty($courseSet['cover']['large']) ? '' : $courseSet['cover']['large'];
        $courseSetImg = array(
            'smallPicture' => $this->getFileUrl($smallPicture, 'course.png'),
            'middlePicture' => $this->getFileUrl($middlePicture, 'course.png'),
            'largePicture' => $this->getFileUrl($largePicture, 'course.png'),
        );
        $courseSet = array_merge($courseSet, $courseSetImg);

        foreach ($copyKeys as $value) {
            $course[$value] = isset($courseSet[$value]) ? $courseSet[$value] : '';
        }

        $course['tags'] = TagUtil::buildTags('course-set', $courseSet['id']);
        $course['tags'] = ArrayToolkit::column($course['tags'], 'name');
        $course['summary'] = $this->filterHtml($course['summary']);

        $course = CourseTitleUtils::formatTitle($course, $courseSet['title']);

        unset($course['courseSet']);

        return $course;
    }

    public function simplify($res)
    {
        $simple = array();

        $courseSet = $this->getCourseSetService()->getCourseSet($res['courseSetId']);
        $smallPicture = empty($courseSet['cover']['small']) ? '' : $courseSet['cover']['small'];

        $simple['id'] = $res['id'];
        $simple['title'] = $res['title'];
        $simple['picture'] = $this->getFileUrl($smallPicture, 'course.png');
        $simple['convNo'] = $this->getConversation($res['id']);

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
        return $this->createService('System:SettingService');
    }

    protected function getConversationService()
    {
        return $this->createService('IM:ConversationService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
