<?php
namespace Topxia\Service\Course\Job;

use Topxia\Service\Crontab\Job;
use Topxia\Service\Common\ServiceKernel;

class LiveLessonStartNotifyJob implements Job
{
    public function execute($params)
    {
        $targetType = $params['targetType'];
        $targetId   = $params['targetId'];
        if ($targetType == 'live_lesson') {
            $lesson = $this->getCourseService()->getLesson($targetId);
            $course = $this->getCourseService()->getCourse($lesson['courseId']);

            $lesson['course'] = $course;
            $message = "您报名的课程$lesson['title']，即将于".date('h:i', $lesson['startTime'])."开始直播，马上前往直播教室准备学习吧!";
            $convNo = $this->getConversationService()->getConversationByTarget($lesson['courseId'], 'course-push');

            $from = array(
                'courseId' => $lesson['courseId'],
                'lessonId' => $targetId
            );
            $to = array(
               'type'   => 'user',
               'convNo' => $convNo 
            );
            $body = array(
                'message'  => $message,
                'courseId' => $lesson['courseId'],
                'lessonId' => $targetId
            );
            
            return $this->pushIM($form, $to, $body);
        }   
    }

    //FIXME 该方法和PushMessageEventSubscriber的一样，最好抽提出来
    protected function pushIM($from, $to, $body)
    {
        $setting = $this->getSettingService()->get('app_im', array());
        if (empty($setting['enabled'])) {
            return;
        }

        $params = array(
            'fromId'   => 0,
            'fromName' => '系统消息',
            'toName'   => '全部',
            'body'     => array(
                'v' => 1,
                't' => 'push',
                'b' => $body,
                's' => $from,
                'd' => $to
            ),
            'convNo'   => empty($to['convNo']) ? '' : $to['convNo']
        );

        if ($to['type'] == 'user') {
            $params['toId'] = $to['id'];
        }

        if (empty($params['convNo'])) {
            return;
        }

        try {
            $api    = IMAPIFactory::create();
            $result = $api->post('/push', $params);

            $setting = $this->getSettingService()->get('developer', array());
            if (!empty($setting['debug'])) {
                IMAPIFactory::getLogger()->debug('API RESULT', !is_array($result) ? array() : $result);
            }
        } catch (\Exception $e) {
            IMAPIFactory::getLogger()->warning('API REQUEST ERROR:'.$e->getMessage());
        }
    }

    protected function pushCloud($eventName, array $data, $level = 'normal')
    {
        return $this->getCloudDataService()->push('school.'.$eventName, $data, time(), $level);
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
