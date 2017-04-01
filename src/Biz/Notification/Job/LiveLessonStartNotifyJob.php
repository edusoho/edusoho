<?php

namespace Biz\Notification\Job;

use Biz\Crontab\Service\Job;
use Topxia\Service\Common\ServiceKernel;
use Biz\CloudPlatform\IMAPIFactory;

class LiveLessonStartNotifyJob implements Job
{
    public function execute($params)
    {
        $targetType = $params['targetType'];
        $targetId = $params['targetId'];
        if ($targetType == 'live_lesson') {
            $lesson = $this->getTaskService()->getTask($targetId);
            $activity = $this->getActivityService()->getActivity($lesson['activityId']);

            $message = '您报名的《'.$lesson['title'].'》课程将于'.date('H:i', $activity['startTime']).'开始直播，点击学习吧';

            $classrooms = $this->getClassroomService()->findClassroomsByCourseId($lesson['courseId']);
            if (empty($classrooms)) {
                $this->pushForClassroomOrCourse($message, $lesson['title'], $lesson['id'], $lesson['courseId']);
            } else {
                foreach ($classrooms as $classroom) {
                    $this->pushForClassroomOrCourse($message, $lesson['title'], $lesson['id'], $lesson['courseId'], $classroom['id']);
                }
            }

            return true;
        }
    }

    protected function pushForClassroomOrCourse($message, $lessonTitle, $lessonId, $courseId, $classroomId = null)
    {
        $conv = array();
        if (empty($classroomId)) {
            $conv = $this->getConversationService()->getConversationByTarget($courseId, 'course-push');
        } else {
            $conv = $this->getConversationService()->getConversationByTarget($classroomId, 'classroom-push');
        }

        $from = array(
            'type' => 'lesson',
            'id' => $lessonId,
        );
        $to = array(
            'type' => 'lesson',
            'id' => 'all',
        );
        $body = array(
            'type' => 'live_start',
            'courseId' => $courseId,
            'lessonId' => $lessonId,
            'lessonTitle' => $lessonTitle,
            'message' => $message,
        );
        if (!empty($classroomId)) {
            $body['classroomId'] = $classroomId;
        }

        $this->pushIM($from, $to, $body, $conv['no']);
    }

    protected function pushIM($from, $to, $body, $convNo = '')
    {
        $setting = $this->getSettingService()->get('app_im', array());
        if (empty($setting['enabled'])) {
            return;
        }

        $params = array(
            'fromId' => 0,
            'fromName' => '系统消息',
            'toName' => '全部',
            'body' => array(
                'v' => 1,
                't' => 'push',
                'b' => $body,
                's' => $from,
                'd' => $to,
            ),
            'convNo' => $convNo,
        );

        if ($to['type'] == 'user') {
            $params['toId'] = $to['id'];
        }
        if (empty($params['convNo'])) {
            return;
        }

        try {
            $api = IMAPIFactory::create();
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
        return $this->getServiceKernel()->createService('IM:ConversationService');
    }

    private function getActivityService()
    {
        return $this->getServiceKernel()->createService('Activity:ActivityService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System:SettingService');
    }

    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task:TaskService');
    }

    private function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
