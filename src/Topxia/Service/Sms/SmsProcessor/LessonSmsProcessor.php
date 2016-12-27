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

        $api                = CloudAPIFactory::create('root');

        global $kernel;
        $router = $kernel->getContainer()->get('router');
        $site = $this->getSettingService()->get('site');
        $url = empty($site['url']) ? $site['url'] : rtrim($site['url'], ' \/');
        for ($i = 0; $i <= intval($count / 1000); $i++) {
            $urls[$i] = empty($url) ? $router->generate('edu_cloud_sms_send_callback', array('targetType' => 'lesson', 'targetId' => $targetId), true) : $url.$router->generate('edu_cloud_sms_send_callback', array('targetType' => 'lesson', 'targetId' => $targetId));
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
        $lesson             = $this->getCourseService()->getLesson($targetId);
        if (empty($lesson)) {
            throw new \RuntimeException($this->getKernel()->trans('课时不存在'));
        }

        global $kernel;
        $site = $this->getSettingService()->get('site');
        $url = empty($site['url']) ? $site['url'] : rtrim($site['url'], ' \/');
        $originUrl = empty($url) ? $kernel->getContainer()->get('router')->generate('course_learn', array('id' => $lesson['courseId']), true) : $url.$kernel->getContainer()->get('router')->generate('course_learn', array('id' => $lesson['courseId']));
        $originUrl .= '#lesson/'.$lesson['id'];

        $shortUrl = SmsToolkit::getShortLink($originUrl);
        $url      = empty($shortUrl) ? $originUrl : $shortUrl;

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
        $parameters['lesson_title'] = $this->getKernel()->trans('课时：').'《'.$lesson['title'].'》';

        if ($lesson['type'] == 'live') {
            $parameters['startTime'] = date("Y-m-d H:i:s", $lesson['startTime']);
        }

        $course['title']            = StringToolkit::cutter($course['title'], 20, 15, 4);
        $parameters['course_title'] = $this->getKernel()->trans('课程：').'《'.$course['title'].'》';

        if ($smsType == 'sms_normal_lesson_publish' || $smsType == 'sms_live_lesson_publish') {
            $description = $parameters['course_title'].' '.$parameters['lesson_title'].$this->getKernel()->trans('发布');
        } else {
            $description = $parameters['course_title'].' '.$parameters['lesson_title'].$this->getKernel()->trans('预告');
        }

        $parameters['url'] = $url.' ';

        $this->getLogService()->info('sms', $smsType, $description, array($to));

        return array('mobile' => $to, 'category' => $smsType, 'sendStyle' => 'templateId', 'description' => $description, 'parameters' => $parameters);
    }

    protected function getLogService()
    {
        return ServiceKernel::instance()->createService('System.LogService');
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
    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
