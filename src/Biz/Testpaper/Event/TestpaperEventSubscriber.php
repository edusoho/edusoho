<?php

namespace Biz\Testpaper\Event;

use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestpaperEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'exam.finish' => 'onTestpaperFinish',
            'exam.reviewed' => 'onTestpaperReviewd',
        );
    }

    public function onTestpaperFinish(Event $event)
    {
        $paperResult = $event->getSubject();

        $biz = $this->getBiz();
        $user = $biz['user'];

        $itemCount = $this->getTestpaperService()->searchItemCount(array(
            'testId' => $paperResult['testId'],
            'questionTypes' => array('essay'),
        ));

        if ($itemCount) {
            $course = $this->getCourseService()->getCourse($paperResult['courseId']);

            $message = array(
                'id' => $paperResult['id'],
                'courseId' => $paperResult['courseId'],
                'name' => $paperResult['paperName'],
                'userId' => $user['id'],
                'userName' => $user['nickname'],
                'testpaperType' => $paperResult['type'],
                'type' => 'perusal',
            );

            if (!empty($course['teacherIds'])) {
                foreach ($course['teacherIds'] as $receiverId) {
                    $result = $this->getNotificationService()->notify($receiverId, 'test-paper', $message);
                }
            }
        }
    }

    public function onTestpaperReviewd(Event $event)
    {
        $paperResult = $event->getSubject();

        $biz = $this->getBiz();
        $user = $biz['user'];

        $itemResults = $this->getTestpaperService()->findItemResultsByResultId($paperResult['id']);
        $itemResults = ArrayToolkit::group($itemResults, 'status');

        if (!empty($itemResults['right'])) {
            $rightItemCount = count($itemResults['right']);
            $this->getTestpaperService()->updateTestpaperResult($paperResult['id'], array('rightItemCount' => $rightItemCount));
        }

        if (!in_array($paperResult['type'], array('testpaper', 'homework'))) {
            return;
        }

        $message = array(
            'id' => $paperResult['id'],
            'courseId' => $paperResult['courseId'],
            'name' => $paperResult['paperName'],
            'userId' => $user['id'],
            'userName' => $user['nickname'],
            'type' => 'read',
            'testpaperType' => $paperResult['type'],
        );

        $result = $this->getNotificationService()->notify($paperResult['userId'], 'test-paper', $message);
    }

    public function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }

    public function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    public function getNotificationService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }

    public function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    public function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    public function getStatusService()
    {
        return $this->getBiz()->service('User:StatusService');
    }
}
