<?php

namespace Biz\Testpaper\Event;

use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestpaperEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'answer.submitted' => ['onAnswerSubmitted', 200],
            'answer.finished' => ['onAnswerFinished', 200],
        ];
    }

    public function onAnswerSubmitted(Event $event)
    {
        $answerRecord = $event->getSubject();
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerRecord['answer_scene_id']);
        $this->processAnswerReportPassed($activity, $answerRecord);
        if ('reviewing' == $answerRecord['status']) {
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
        } else {
            $this->processComment($activity, $answerRecord);
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
        $this->processAnswerReportPassed($activity, $answerRecord);
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
        $this->notify($answerRecord);
    }

    protected function processAnswerReportPassed($activity, $answerRecord)
    {
        if (empty($activity['mediaType']) || empty($answerRecord)) {
            return;
        }
        if ('homework' == $activity['mediaType']) {
            if ('submit' === $activity['finishType'] && in_array($answerRecord['status'], [AnswerService::ANSWER_RECORD_STATUS_REVIEWING, AnswerService::ANSWER_RECORD_STATUS_FINISHED])) {
                $this->getAnswerReportService()->update($answerRecord['answer_report_id'], ['grade' => 'passed']);
                return;
            }
            $answerReport = $this->getAnswerReportService()->getSimple($answerRecord['answer_report_id']);
            if (AnswerService::ANSWER_RECORD_STATUS_FINISHED == $answerRecord['status'] && 'score' === $activity['finishType']) {
                if ($answerReport['score'] >= $activity['finishData']) {
                    $this->getAnswerReportService()->update($answerRecord['answer_report_id'], ['grade' => 'passed']);
                } else {
                    $this->getAnswerReportService()->update($answerRecord['answer_report_id'], ['grade' => 'unpassed']);
                }
            }
        }

        if ('testpaper' == $activity['mediaType']) {
            $answerReport = $this->getAnswerReportService()->getSimple($answerRecord['answer_report_id']);
            $answerScene = $this->getAnswerSceneService()->get($answerReport['answer_scene_id']);
            if (AnswerService::ANSWER_RECORD_STATUS_FINISHED == $answerRecord['status']) {
                if ($answerReport['score'] >= $answerScene['pass_score']) {
                    $this->getAnswerReportService()->update($answerRecord['answer_report_id'], ['grade' => 'passed']);
                } else {
                    $this->getAnswerReportService()->update($answerRecord['answer_report_id'], ['grade' => 'unpassed']);
                }
            }
        }
    }

    protected function processComment($activity, $answerRecord)
    {
        if ('testpaper' == $activity['mediaType'] && $testPaper = $this->getTestpaperActivityService()->getActivity($activity['mediaId'])) {
            $comment = '';
            $answerReport = $this->getAnswerReportService()->get($answerRecord['answer_report_id']);
            foreach ($testPaper['customComments'] as $customComment) {
                if ($customComment['start'] <= $answerReport['score'] && $answerReport['score'] <= $customComment['end']) {
                    $comment = $customComment['comment'];
                    break;
                }
            }
            if ($comment) {
                $this->getAnswerReportService()->update($answerReport['id'], ['comment' => $comment]);
                $this->notify($answerRecord);
            }
        }
    }

    protected function notify($answerRecord)
    {
        $user = $this->getBiz()['user'];
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerRecord['answer_scene_id']);
        $message = [
            'id' => $answerRecord['id'],
            'courseId' => $activity['fromCourseId'],
            'name' => $activity['title'],
            'userId' => $user['id'],
            'userName' => $user['nickname'],
            'type' => $activity['mediaType'],
            'mode' => 'create',
        ];
        $this->getNotificationService()->notify($answerRecord['user_id'], 'answer-comment', $message);
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
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

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->getBiz()->service('Activity:TestpaperActivityService');
    }
}
