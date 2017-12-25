<?php

namespace Biz\Notification\Job;

use Biz\CloudPlatform\CloudAPIFactory;
use Biz\OpenCourse\Service\OpenCourseService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class LiveOpenPushNotificationOneHourJob extends AbstractJob
{
    public function execute()
    {
        $targetType = $this->args['targetType'];
        $targetId = $this->args['targetId'];

        if ($targetType == 'liveOpenLesson') {
            $lesson = $this->getOpenCourseService()->getLesson($targetId);
            $course = $this->getOpenCourseService()->getCourse($lesson['courseId']);
        }

        $from = array(
            'type' => 'course',
            'id' => $course['id'],
            'image' => $this->getFileUrl($course['smallPicture']),
        );

        $to = array('type' => 'course', 'id' => $course['id']);

        $body = array('type' => 'live.notify', 'id' => $lesson['id'], 'lessonType' => $lesson['type']);

        $this->push($course['title'], $lesson['title'], $from, $to, $body);
    }

    protected function push($title, $content, $from, $to, $body)
    {
        $message = array(
            'title' => $title,
            'content' => $content,
            'custom' => array(
                'from' => $from,
                'to' => $to,
                'body' => $body,
            ),
        );

        $result = CloudAPIFactory::create('tui')->post('/message/send', $message);
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->biz->service('OpenCourse:OpenCourseService');
    }

    protected function getFileUrl($path)
    {
        if (empty($path)) {
            return $path;
        }

        $path = str_replace('public://', '', $path);
        $path = str_replace('files/', '', $path);
        $path = "http://{$_SERVER['HTTP_HOST']}/files/{$path}";

        return $path;
    }
}
