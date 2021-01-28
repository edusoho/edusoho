<?php

namespace Biz\Sms\SmsProcessor;

use AppBundle\Common\SmsToolkit;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\StringToolkit;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\OpenCourse\OpenCourseException;
use Biz\OpenCourse\Service\OpenCourseService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class LiveOpenLessonSmsProcessor extends BaseSmsProcessor
{
    public function getUrls($targetId, $smsType)
    {
        $lesson = $this->getOpenCourseService()->getLesson($targetId);
        $course = $this->getOpenCourseService()->getCourse($lesson['courseId']);
        $count = 0;

        $count = $this->getOpenCourseService()->countMembers(array('courseId' => $course['id']));

        global $kernel;
        $api = CloudAPIFactory::create('root');

        $site = $this->getSettingService()->get('site');
        $url = empty($site['url']) ? $site['url'] : rtrim($site['url'], ' \/');
        for ($i = 0; $i <= intval($count / 1000); ++$i) {
            $urls[$i] = empty($url) ? $kernel->getContainer()->get('router')->generate('edu_cloud_sms_send_callback', array('targetType' => 'liveOpenLesson', 'targetId' => $targetId), UrlGeneratorInterface::ABSOLUTE_URL) : $url.$kernel->getContainer()->get('router')->generate('edu_cloud_sms_send_callback', array('targetType' => 'liveOpenLesson', 'targetId' => $targetId));
            $urls[$i] .= '?index='.($i * 1000);
            $urls[$i] .= '&smsType='.$smsType;
            $sign = $this->getSignEncoder()->encodePassword($urls[$i], $api->getAccessKey());
            $sign = rawurlencode($sign);
            $urls[$i] .= '&sign='.$sign;
        }

        return array('count' => $count, 'urls' => $urls);
    }

    public function getSmsInfo($targetId, $index, $smsType)
    {
        $lesson = $this->getOpenCourseService()->getLesson($targetId);

        if (empty($lesson)) {
            throw OpenCourseException::NOTFOUND_LESSON();
        }

        global $kernel;
        $site = $this->getSettingService()->get('site');
        $url = empty($site['url']) ? $site['url'] : rtrim($site['url'], ' \/');

        $originUrl = empty($url) ? $kernel->getContainer()->get('router')->generate('open_course_show', array('courseId' => $lesson['courseId']), UrlGeneratorInterface::ABSOLUTE_URL) : $url.$kernel->getContainer()->get('router')->generate('open_course_show', array('courseId' => $lesson['courseId']));

        $shortUrl = SmsToolkit::getShortLink($originUrl);
        $url = empty($shortUrl) ? $originUrl : $shortUrl;
        $course = $this->getOpenCourseService()->getCourse($lesson['courseId']);
        $to = '';

        $students = $this->getOpenCourseService()->searchMembers(array('courseId' => $course['id']), array('createdTime' => 'Desc'), $index, 1000);

        $to = array_filter(ArrayToolkit::column($students, 'mobile'));
        $to = implode(',', $to);

        $parameters['lesson_title'] = '';

        if ('liveOpen' == $lesson['type']) {
            $parameters['startTime'] = date('Y-m-d H:i:s', $lesson['startTime']);
        }

        $course['title'] = StringToolkit::cutter($course['title'], 20, 15, 4);
        $parameters['course_title'] = '直播公开课：《'.$course['title'].'》';

        $description = $parameters['course_title'].' '.$parameters['lesson_title'].'预告';

        $parameters['url'] = $url.' ';

        return array('mobile' => $to, 'category' => $smsType, 'sendStyle' => 'templateId', 'description' => $description, 'parameters' => $parameters);
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

    protected function getSignEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }
}
