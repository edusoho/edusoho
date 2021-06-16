<?php

namespace Biz\WrongBook\Event;

use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\WrongBook\Service\WrongQuestionService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WrongQuestionSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'answer.submitted' => 'onAnswerSubmitted',
        ];
    }

    public function onAnswerSubmitted(Event $event)
    {
        $answerRecord = $event->getSubject();
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerRecord['answer_scene_id']);

        if (empty($activity) || !in_array($activity['mediaType'], ['testpaper', 'homework', 'exercise'])) {
            return;
        }

        $course = $this->getCourseService()->getCourse($activity['fromCourseId']);

        $wrongAnswerQuestionReports = $this->getAnswerQuestionReportService()->search([
            'answer_record_id' => $answerRecord['id'],
            'status' => 'wrong',
        ], [], 0, PHP_INT_MAX);

        $this->getWrongQuestionService()->batchBuildWrongQuestion($wrongAnswerQuestionReports, [
            'user_id' => $answerRecord['user_id'],
            'target_type' => $this->getTargetType($activity),
            'target_id' => $course['id'],
        ]);
    }

    protected function getTargetType($activity)
    {
        $targetType = $activity['mediaType'];

        if (in_array($activity['mediaType'], ['testpaper', 'homework'])) {
            $courseSet = $this->getCourseSetService()->getCourseSet($activity['fromCourseId']);
            if ($courseSet['isClassroomRef']) {
                $targetType = 'class';
            } else {
                $targetType = 'course';
            }
        }

        return $targetType;
    }

    /**
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return $this->getBiz()->service('WrongBook:WrongQuestionService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerQuestionReportService');
    }
}
