<?php

namespace Biz\Testpaper\Event;

use AppBundle\Common\StringToolkit;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestpaperEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'testpaper.finish'   => 'onTestpaperFinish',
            'testpaper.reviewed' => 'onTestpaperReviewd'
        );
    }

    public function onTestpaperFinish(Event $event)
    {
        $paperResult = $event->getSubject();

        $biz  = $this->getBiz();
        $user = $biz['user'];

        $itemCount = $this->getTestpaperService()->searchItemCount(array(
            'testId'        => $paperResult['testId'],
            'questionTypes' => array('essay')
        ));

        if ($itemCount) {
            $course = $this->getCourseService()->getCourse($paperResult['courseId']);

            $message = array(
                'id'       => $paperResult['id'],
                'courseId' => $paperResult['courseId'],
                'name'     => $paperResult['paperName'],
                'userId'   => $user['id'],
                'userName' => $user['nickname'],
                'type'     => 'perusal'
            );

            foreach ($course['teacherIds'] as $receiverId) {
                $result = $this->getNotificationService()->notify($receiverId, 'test-paper', $message);
            }
        }

        //$this->sendStatus($paperResult, 'finished_testpaper');
    }

    public function onTestpaperReviewd(Event $event)
    {
        $paperResult = $event->getSubject();

        $biz  = $this->getBiz();
        $user = $biz['user'];

        if (!in_array($paperResult['type'], array('testpaper', 'homework'))) {
            return;
        }

        $message = array(
            'id'            => $paperResult['id'],
            'courseId'      => $paperResult['courseId'],
            'name'          => $paperResult['paperName'],
            'userId'        => $user['id'],
            'userName'      => $user['nickname'],
            'type'          => 'read',
            'testpaperType' => $paperResult['type']
        );

        $result = $this->getNotificationService()->notify($paperResult['userId'], 'test-paper', $message);

        $this->sendStatus($paperResult, "reviewed_{$paperResult['type']}");
    }

    protected function sendStatus($testpaperResult, $type)
    {
        $course    = $this->getCourseService()->getCourse($testpaperResult['courseId']);
        $activity  = $this->getActivityService()->getActivity($testpaperResult['lessonId']);
        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        $private   = $course['status'] == 'published' ? 0 : 1;
        $classroom = array();

        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
            $classroom = $this->getClassroomService()->getClassroom($classroom['classroomId']);

            if (array_key_exists('showable', $classroom) && $classroom['showable'] == 1) {
                $private = 0;
            } else {
                $private = 1;
            }
        }

        $this->getStatusService()->publishStatus(array(
            'userId'      => $testpaperResult['userId'],
            'courseId'    => $course['id'],
            'classroomId' => $classroom ? $classroom['id'] : 0,
            'type'        => $type,
            'objectType'  => $testpaper['type'],
            'objectId'    => $testpaper['id'],
            'private'     => $private,
            'properties'  => array(
                'testpaper' => $this->simplifyTestpaper($testpaper),
                'result'    => $this->simplifyTestpaperResult($testpaperResult),
                'activity'  => $this->simplifyActivity($activity),
                'version'   => '2.0'
            )
        ));
    }

    protected function simplifyTestpaper($testpaper)
    {
        return array(
            'id'          => $testpaper['id'],
            'name'        => $testpaper['name'],
            'description' => StringToolkit::plain($testpaper['description'], 100),
            'score'       => $testpaper['score'],
            'passedScore' => $testpaper['passedCondition'],
            'itemCount'   => $testpaper['itemCount']
        );
    }

    protected function simplifyTestpaperResult($testpaperResult)
    {
        return array(
            'id'              => $testpaperResult['id'],
            'userId'          => $testpaperResult['userId'],
            'score'           => $testpaperResult['score'],
            'objectiveScore'  => $testpaperResult['objectiveScore'],
            'subjectiveScore' => $testpaperResult['subjectiveScore'],
            'teacherSay'      => StringToolkit::plain($testpaperResult['teacherSay'], 100),
            'passedStatus'    => $testpaperResult['passedStatus']
        );
    }

    protected function simplifyActivity($activity)
    {
        return array(
            'id'      => $activity['id'],
            'type'    => $activity['mediaType'],
            'title'   => $activity['title'],
            'summary' => StringToolkit::plain($activity['content'], 100)
        );
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
