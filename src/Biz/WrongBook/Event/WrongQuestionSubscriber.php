<?php

namespace Biz\WrongBook\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseSetService;
use Biz\ItemBankExercise\Service\ChapterExerciseRecordService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
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

        list($targetType, $targetId) = $this->getWrongQuestionSource($answerRecord);

        $wrongAnswerQuestionReports = $this->getAnswerQuestionReportService()->search([
            'answer_record_id' => $answerRecord['id'],
            'status' => 'wrong',
        ], [], 0, PHP_INT_MAX);

        $this->getWrongQuestionService()->batchBuildWrongQuestion($wrongAnswerQuestionReports, [
            'user_id' => $answerRecord['user_id'],
            'answer_scene_id' => $answerRecord['answer_scene_id'],
            'testpaper_id' => $this->getTestPaperId($answerRecord),
            'target_type' => $targetType,
            'target_id' => $targetId,
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

    protected function getTestPaperId($answerRecord)
    {
        $testPaperId = $answerRecord['assessment_id'];

        $isChapterRecord = $this->getItemBankChapterExerciseRecordService()->getByAnswerRecordId($answerRecord['id']);
        if ($isChapterRecord) {
            $testPaperId = $isChapterRecord['itemCategoryId'];
        }

        return $testPaperId;
    }

    protected function getWrongQuestionSource($answerRecord)
    {
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerRecord['answer_scene_id']);

        if (!empty($activity) && in_array($activity['mediaType'], ['testpaper', 'homework', 'exercise'])) {
            $courseSet = $this->getCourseSetService()->getCourseSet($activity['fromCourseSetId']);
            if ($courseSet['isClassroomRef']) {
                $classCourse = $this->getClassroomService()->getClassroomCourseByCourseSetId($courseSet['id']);
                $targetType = 'classroom';
                $targetId = $classCourse['classroomId'];
            } else {
                $targetType = 'course';
                $targetId = $activity['fromCourseSetId'];
            }
        } elseif ($assessmentExerciseRecord = $this->getExerciseModuleService()->getByAnswerSceneId($answerRecord['answer_scene_id'])) {
            $targetType = 'exercise';
            $targetId = $assessmentExerciseRecord['exerciseId'];
        } else {
            $targetType = 'wrong_book_exercise';
            $targetId = 0;
        }

        return [$targetType, $targetId];
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
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerQuestionReportService');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getExerciseModuleService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseModuleService');
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

    /**
     * @return ChapterExerciseRecordService
     */
    protected function getItemBankChapterExerciseRecordService()
    {
        return $this->getBiz()->service('ItemBankExercise:ChapterExerciseRecordService');
    }
}
