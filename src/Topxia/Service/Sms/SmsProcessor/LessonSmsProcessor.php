<?php
namespace Topxia\Service\Sms\SmsProcessor;

use Topxia\Common\SmsToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class LessonSmsProcessor extends BaseProcessor implements SmsProcessor
{
    public function getUrls($targetId, $smsType)
    {
        $lesson = $this->getCourseService()->getLesson($targetId);
        $course = $this->getCourseService()->getCourse($lesson['courseId']);
        $count  = 0;

        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);

            if ($classroom) {
                $count = $this->getClassroomService()->searchMemberCount(array('classroomId' => $classroom['classroomId']));
            }
        } else {
            $count = $this->getCourseService()->searchMemberCount(array('courseId' => $course['id']));
        }

        global $kernel;
        $container          = $kernel->getContainer();
        $siteSetting        = $this->getSettingService()->get('site');
        $siteSetting['url'] = rtrim($siteSetting['url']);
        $siteSetting['url'] = rtrim($siteSetting['url'], '/');
        $hostName           = $siteSetting['url'];
        $api                = CloudAPIFactory::create('root');

        for ($i = 0; $i <= intval($count / 1000); $i++) {
            $urls[$i] = $hostName;
            $urls[$i] .= $container->get('router')->generate('edu_cloud_sms_send_callback', array('targetType' => 'lesson', 'targetId' => $targetId));
            $urls[$i] .= '?index='.($i * 1000);
            $urls[$i] .= '&smsType='.$smsType;
            $sign = $this->getSignEncoder()->encodeSign($urls[$i], $api->getAccessKey());
            $sign = rawurlencode($sign);
            $urls[$i] .= '&sign='.$sign;
        }

        return array('count' => $count, 'urls' => $urls);
    }

    public function getSmsInfo($targetId, $index, $smsType)
    {
        global $kernel;
        $siteSetting        = $this->getSettingService()->get('site');
        $siteSetting['url'] = rtrim($siteSetting['url']);
        $siteSetting['url'] = rtrim($siteSetting['url'], '/');
        $hostName           = $siteSetting['url'];
        $lesson             = $this->getCourseService()->getLesson($targetId);

        if (empty($lesson)) {
            throw new \RuntimeException('课时不存在');
        }

        $originUrl = $hostName;
        $originUrl .= $kernel->getContainer()->get('router')->generate('course_learn', array('id' => $lesson['courseId']));
        $originUrl .= '#lesson/'.$lesson['id'];

        $shortUrl = SmsToolkit::getShortLink($originUrl);
        $url      = empty($shortUrl) ? $hostName.$kernel->getContainer()->get('router')->generate('course_show', array('id' => $lesson['courseId'])) : $shortUrl;

        $course = $this->getCourseService()->getCourse($lesson['courseId']);
        $to     = '';

        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);

            if ($classroom) {
                $students = $this->getClassroomService()->searchMembers(array('classroomId' => $classroom['classroomId']), array('createdTime', 'Desc'), $index, 1000);
            }
        } else {
            $students = $this->getCourseService()->searchMembers(array('courseId' => $course['id']), array('createdTime', 'Desc'), $index, 1000);
        }

        $studentIds = ArrayToolkit::column($students, 'userId');
        $to         = $this->getUsersMobile($studentIds);

        $lesson['title']            = StringToolkit::cutter($lesson['title'], 20, 15, 4);
        $parameters['lesson_title'] = '课时：《'.$lesson['title'].'》';

        if ($lesson['type'] == 'live') {
            $parameters['startTime'] = date("Y-m-d H:i:s", $lesson['startTime']);
        }

        $course['title']            = StringToolkit::cutter($course['title'], 20, 15, 4);
        $parameters['course_title'] = '课程：《'.$course['title'].'》';

        if ($smsType == 'sms_normal_lesson_publish' || $smsType == 'sms_live_lesson_publish') {
            $description = $parameters['course_title'].' '.$parameters['lesson_title'].'发布';
        } else {
            $description = $parameters['course_title'].' '.$parameters['lesson_title'].'预告';
        }

        $parameters['url'] = $url.' ';

        return array('mobile' => $to, 'category' => $smsType, 'description' => $description, 'parameters' => $parameters);
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
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
