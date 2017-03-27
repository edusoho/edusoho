<?php
namespace Topxia\Service\Sms\SmsProcessor;

use Topxia\Common\SmsToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class LiveOpenLessonSmsProcessor extends BaseProcessor implements SmsProcessor
{
    public function getUrls($targetId, $smsType)
    {
        $lesson = $this->getOpenCourseService()->getLesson($targetId);
        $course = $this->getOpenCourseService()->getCourse($lesson['courseId']);
        $count  = 0;

        $count = $this->getOpenCourseService()->searchMemberCount(array('courseId' => $course['id']));

        global $kernel;
        $api                = CloudAPIFactory::create('root');

        $site = $this->getSettingService()->get('site');
        $url = empty($site['url']) ? $site['url'] : rtrim($site['url'], ' \/');
        for ($i = 0; $i <= intval($count / 1000); $i++) {
            $urls[$i] = empty($url)? $kernel->getContainer()->get('router')->generate('edu_cloud_sms_send_callback', array('targetType' => 'liveOpenLesson', 'targetId' => $targetId), true): $url.$kernel->getContainer()->get('router')->generate('edu_cloud_sms_send_callback', array('targetType' => 'liveOpenLesson', 'targetId' => $targetId));
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
        $lesson             = $this->getOpenCourseService()->getLesson($targetId);

        if (empty($lesson)) {
            throw new \RuntimeException('课时不存在');
        }

        global $kernel;
        $site = $this->getSettingService()->get('site');
        $url = empty($site['url']) ? $site['url'] : rtrim($site['url'], ' \/');

        $originUrl = empty($url)? $kernel->getContainer()->get('router')->generate('open_course_show', array('courseId' => $lesson['courseId']), true) : $url.$kernel->getContainer()->get('router')->generate('open_course_show', array('courseId' => $lesson['courseId']));

        $shortUrl = SmsToolkit::getShortLink($originUrl);
        $url      = empty($shortUrl) ? $originUrl : $shortUrl;
        $course   = $this->getOpenCourseService()->getCourse($lesson['courseId']);
        $to       = '';

        $students = $this->getOpenCourseService()->searchMembers(array('courseId' => $course['id']), array('createdTime', 'Desc'), $index, 1000);

        $to = array_filter(ArrayToolkit::column($students, 'mobile'));
        $to = implode(',', $to);

        $parameters['lesson_title'] = '';

        if ($lesson['type'] == 'liveOpen') {
            $parameters['startTime'] = date("Y-m-d H:i:s", $lesson['startTime']);
        }

        $course['title']            = StringToolkit::cutter($course['title'], 20, 15, 4);
        $parameters['course_title'] = '直播公开课：《'.$course['title'].'》';

        $description = $parameters['course_title'].' '.$parameters['lesson_title'].'预告';

        $parameters['url'] = $url.' ';

        return array('mobile' => $to, 'category' => $smsType, 'sendStyle' => 'templateId', 'description' => $description, 'parameters' => $parameters);
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getOpenCourseService()
    {
        return ServiceKernel::instance()->createService('OpenCourse.OpenCourseService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    protected function getSignEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }
}
