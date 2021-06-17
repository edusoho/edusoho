<?php

namespace Biz\WrongBook\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
use Biz\WrongBook\Dao\WrongQuestionCollectDao;
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
            'wrong_question.batch_create' => 'onWrongQuestionBatchChanged',
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
            'answer_scene_id' => $answerRecord['answer_scene_id'],
            'target_type' => $this->getTargetType($activity),
            'target_id' => $course['id'],
        ]);
    }

    public function onWrongQuestionBatchChanged(Event $event)
    {
        $wrongQuestions = $event->getSubject();

        $collectQuestions = ArrayToolkit::group($wrongQuestions, 'collect_id');

        foreach ($collectQuestions as $collectId => $collectQuestion) {
            $this->getWrongQuestionCollectDao()->wave([$collectId], ['wrong_times' => count($collectQuestion)]);
        }

        $poolId = $event->getArgument('pool_id');

        $poolCollects = $this->getWrongQuestionCollectDao()->search(['pool_id' => $poolId], [], 0, PHP_INT_MAX);

        $poolCounts = $this->getWrongQuestionService()->countWrongQuestion([
            'collect_ids' => ArrayToolkit::column($poolCollects, 'id'),
        ]);

        $this->getWrongQuestionBookPoolDao()->update($poolId, ['item_num' => $poolCounts]);
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

    /**
     * @return WrongQuestionBookPoolDao
     */
    protected function getWrongQuestionBookPoolDao()
    {
        return $this->getBiz()->dao('WrongBook:WrongQuestionBookPoolDao');
    }

    /**
     * @return WrongQuestionCollectDao
     */
    protected function getWrongQuestionCollectDao()
    {
        return $this->getBiz()->dao('WrongBook:WrongQuestionCollectDao');
    }
}
