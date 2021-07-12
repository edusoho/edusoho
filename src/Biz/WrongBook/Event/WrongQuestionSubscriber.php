<?php

namespace Biz\WrongBook\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseSetService;
use Biz\ItemBankExercise\Service\ChapterExerciseRecordService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
use Biz\WrongBook\Dao\WrongQuestionCollectDao;
use Biz\WrongBook\Service\WrongQuestionService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WrongQuestionSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'answer.submitted' => 'onAnswerSubmitted',
            'wrong_question.batch_create' => 'onWrongQuestionBatchChanged',
            'wrong_question.batch_delete' => 'onWrongQuestionBatchDelete',
            'item.delete' => 'onItemDelete',
            'item.batchDelete' => 'onItemBatchDelete',
        ];
    }

    public function onAnswerSubmitted(Event $event)
    {
        $answerRecord = $event->getSubject();

        list($targetType, $targetId) = $this->getWrongQuestionSource($answerRecord);

        $wrongAnswerQuestionReports = $this->getAnswerQuestionReportService()->search([
            'answer_record_id' => $answerRecord['id'],
            'statues' => ['wrong', 'no_answer'],
        ], [], 0, PHP_INT_MAX);
        if ('wrong_book_exercise' === $targetType) {
            $correctAnswerQuestionReports = $this->getAnswerQuestionReportService()->search([
                'answer_record_id' => $answerRecord['id'],
                'statues' => ['right'],
            ], [], 0, PHP_INT_MAX);
            $correctAnswerQuestionReports = ArrayToolkit::index($correctAnswerQuestionReports, 'item_id');
            $correctItems = $this->getItemService()->findItemsByIds(ArrayToolkit::column($correctAnswerQuestionReports, 'item_id'));
            $correctQuestions = [];
            foreach ($correctItems as $item) {
                if ('material' !== $item['type']) {
                    $correctQuestions[] = $correctAnswerQuestionReports[$item['id']];
                }
            }
            $pool = $this->getWrongQuestionService()->getPoolBySceneId($answerRecord['answer_scene_id']);
            if ($pool) {
                $this->getWrongQuestionService()->batchBuildCorrectQuestion($correctQuestions, [
                    'user_id' => $answerRecord['user_id'],
                    'answer_scene_id' => $answerRecord['answer_scene_id'],
                    'testpaper_id' => $this->getTestPaperId($answerRecord),
                    'target_type' => $pool['target_type'],
                    'target_id' => $pool['target_id'],
                ]);
            }
        }

        $wrongAnswerQuestionReports = ArrayToolkit::index($wrongAnswerQuestionReports, 'item_id');
        $items = $this->getItemService()->findItemsByIds(ArrayToolkit::column($wrongAnswerQuestionReports, 'item_id'));

        $wrongQuestion = [];
        foreach ($items as $item) {
            if ('material' !== $item['type']) {
                $wrongQuestion[] = $wrongAnswerQuestionReports[$item['id']];
            }
        }

        $this->getWrongQuestionService()->batchBuildWrongQuestion($wrongQuestion, [
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

        $this->updatePoolItemNum($poolId);
    }

    public function onWrongQuestionBatchDelete(Event $event)
    {
        $wrongQuestionCollects = $event->getSubject();

        $poolIds = array_unique(ArrayToolkit::column($wrongQuestionCollects, 'pool_id'));

        foreach ($poolIds as $poolId) {
            $this->updatePoolItemNum($poolId);
        }
    }

    public function onItemDelete(Event $event)
    {
        $item = $event->getSubject();

        if (!empty($item)) {
            $this->getWrongQuestionService()->batchDeleteWrongQuestionByItemIds([$item['id']]);
        }
    }

    public function onItemBatchDelete(Event $event)
    {
        $items = $event->getSubject();

        if (!empty($items)) {
            $this->getWrongQuestionService()->batchDeleteWrongQuestionByItemIds(ArrayToolkit::column($items, 'id'));
        }
    }

    protected function updatePoolItemNum($poolId)
    {
        $poolCollects = $this->getWrongQuestionCollectDao()->search(['pool_id' => $poolId, 'status' => 'wrong'], [], 0, PHP_INT_MAX);

        $itemNum = count($poolCollects);

        if (0 === $itemNum) {
            $this->getWrongQuestionBookPoolDao()->delete($poolId);
        } else {
            $this->getWrongQuestionBookPoolDao()->update($poolId, ['item_num' => count($poolCollects)]);
        }
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
            if ($courseSet['locked']) {
                $classCourse = $this->getClassroomService()->getClassroomCourseByCourseSetId($courseSet['id']);
                $targetType = 'classroom';
                $targetId = $classCourse['classroomId'];
            } else {
                $targetType = 'course';
                $targetId = $activity['fromCourseSetId'];
            }
        } elseif ($assessmentExerciseRecord = $this->getExerciseModuleService()->getByAnswerSceneId($answerRecord['answer_scene_id'])) {
            $bankExercise = $this->getItemBankExerciseService()->get($assessmentExerciseRecord['exerciseId']);
            $targetType = 'exercise';
            $targetId = empty($bankExercise) ? 0 : $bankExercise['questionBankId'];
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
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseService');
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

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->getBiz()->service('ItemBank:Item:ItemService');
    }
}
