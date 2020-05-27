<?php

namespace Biz\Testpaper\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestpaperEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'answer.submitted' => 'onAnswerSubmitted',
            'answer.finished' => 'onAnswerFinished',
        ];
    }

    public function onAnswerSubmitted(Event $event)
    {
        $answerRecord = $event->getSubject();

        if ('reviewing' == $answerRecord['status']) {
            $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerRecord['answer_scene_id']);
            if (empty($activity['mediaType']) || !in_array($activity['mediaType'], ['homework', 'testpaper'])) {
                return;
            }
            $assessment = $this->getAssessmentService()->getAssessment($answerRecord['assessment_id']);
            $user = $this->getBiz()['user'];
            $message = [
                'id' => $answerRecord['id'],
                'courseId' => $activity['fromCourseId'],
                'name' => $assessment['name'],
                'userId' => $user['id'],
                'userName' => $user['nickname'],
                'testpaperType' => $activity['mediaType'],
                'type' => 'perusal',
            ];

            $course = $this->getCourseService()->getCourse($activity['fromCourseId']);
            if (!empty($course['teacherIds'])) {
                foreach ($course['teacherIds'] as $teacherId) {
                    $this->getNotificationService()->notify($teacherId, 'test-paper', $message);
                }
            }
        }
    }

    public function onAnswerFinished(Event $event)
    {
        $answerReport = $event->getSubject();
        $answerRecord = $this->getAnswerRecordService()->get($answerReport['answer_record_id']);
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerReport['answer_scene_id']);
        if (empty($activity['mediaType']) || !in_array($activity['mediaType'], ['homework', 'testpaper'])) {
            return;
        }

        $assessment = $this->getAssessmentService()->getAssessment($answerRecord['assessment_id']);
        $user = $this->getBiz()['user'];
        $message = [
            'id' => $answerRecord['id'],
            'courseId' => $activity['fromCourseId'],
            'name' => $assessment['name'],
            'userId' => $user['id'],
            'userName' => $user['nickname'],
            'type' => 'read',
            'testpaperType' => $activity['mediaType'],
        ];

        $result = $this->getNotificationService()->notify($answerRecord['user_id'], 'test-paper', $message);
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

    /**
     * @return ActivityService
     */
    public function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    public function getStatusService()
    {
        return $this->getBiz()->service('User:StatusService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AnswerRecordService
     */
    public function getAnswerRecordService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerRecordService');
    }
}
