<?php

namespace Biz\Sms\SmsProcessor;

use AppBundle\Common\SmsToolkit;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\StringToolkit;
use Biz\OpenCourse\OpenCourseException;
use Biz\OpenCourse\Service\OpenCourseService;

class LiveOpenLessonSmsProcessor extends BaseSmsProcessor
{
    public function getSmsParams($targetId, $smsType)
    {
        $lesson = $this->getOpenCourseService()->getLesson($targetId);

        if (empty($lesson)) {
            throw OpenCourseException::NOTFOUND_LESSON();
        }
        $course = $this->getOpenCourseService()->getCourse($lesson['courseId']);
        if (empty($course)) {
            throw OpenCourseException::NOTFOUND_OPENCOURSE();
        }
        $parameters['lesson_title'] = '';

        if ('liveOpen' == $lesson['type']) {
            $parameters['startTime'] = date('Y-m-d H:i:s', $lesson['startTime']);
        }

        $course['title'] = StringToolkit::cutter($course['title'], 20, 15, 4);
        $parameters['course_title'] = '直播公开课：《'.$course['title'].'》';

        global $kernel;
        $site = $this->getSettingService()->get('site');
        $url = empty($site['url']) ? $site['url'] : rtrim($site['url'], ' \/');

        $originUrl = $url . $kernel->getContainer()->get('router')->generate('open_course_show', ['courseId' => $lesson['courseId']]);

        $shortUrl = SmsToolkit::getShortLink($originUrl);
        $url = empty($shortUrl) ? $originUrl : $shortUrl;

        $parameters['url'] = $url.' ';

        return $parameters;
    }

    public function searchUserIds($targetId, $smsType, $start, $limit)
    {
        $lesson = $this->getOpenCourseService()->getLesson($targetId);

        if (empty($lesson)) {
            throw OpenCourseException::NOTFOUND_LESSON();
        }
        $course = $this->getOpenCourseService()->getCourse($lesson['courseId']);
        if (empty($course)) {
            throw OpenCourseException::NOTFOUND_OPENCOURSE();
        }
        $students = $this->getOpenCourseService()->searchMembers(['courseId' => $course['id'], 'role' => 'student'], ['createdTime' => 'ASC'], $start, $limit, ['userId']);

        return ArrayToolkit::column($students, 'userId');
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->getBiz()->service('OpenCourse:OpenCourseService');
    }
}
