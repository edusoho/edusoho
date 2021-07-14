<?php

namespace Biz\WrongBook\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseSetService;
use Biz\ItemBankExercise\Service\ChapterExerciseRecordService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\System\Service\LogService;
use Biz\Task\Service\TaskService;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
use Biz\WrongBook\Dao\WrongQuestionCollectDao;
use Biz\WrongBook\Dao\WrongQuestionDao;
use Biz\WrongBook\Service\WrongQuestionService;
use Biz\WrongBook\WrongBookException;
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
            'wrong_question_pool.delete' => 'onWrongQuestionPoolDelete',
            'item.delete' => 'onItemDelete',
            'item.batchDelete' => 'onItemBatchDelete',
        ];
    }

    public function onWrongQuestionPoolDelete(Event $event)
    {
        $contions = $event->getSubject();
        if (empty($contions['target_id'])) {
            throw WrongBookException::WRONG_QUESTION_BOOK_POOL_TARGET_ID_REQUIRE();
        }
        $wrongPools = $this->getWrongQuestionBookPoolDao()->findPoolsByTargetIdAndTargetType($contions['target_id'], $contions['target_type']);
        if (empty($wrongPools)) {
            throw WrongBookException::WRONG_QUESTION_BOOK_POOL_NOT_EXIST();
        }
        $wrongPoolIds = ArrayToolkit::column($wrongPools, 'id');
        $db = $this->getBiz()->offsetGet('db');
        try {
            $db->beginTransaction();

            $this->getWrongQuestionBookPoolDao()->deleteWrongPoolByTargetIdAndTargetType($contions['target_id'], $contions['target_type']);
            $collecIds = $this->getWrongQuestionCollectDao()->getCollectIdsBYPoolIds($wrongPoolIds);
            $this->getWrongQuestionCollectDao()->deleteCollectByPoolIds($wrongPoolIds);
            $collecIds = ArrayToolkit::column($collecIds, 'id');
            $this->getWrongQuestionDao()->batchDelete(['collect_ids' => $collecIds]);
            $this->getLogService()->info(
                'wrong_question',
                'delete_wrong_question',
                "删除课程#{$contions['target_id']}错题池"
            );

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    public function onAnswerSubmitted(Event $event)
    {
        $answerRecord = $event->getSubject();

        list($targetType, $targetId, $sourceType, $sourceId) = $this->getWrongQuestionSource($answerRecord);

        if ('wrong_book_exercise' === $targetType) {
            $this->dealWrongQuestionExerciseQuestions($answerRecord, $targetType, $targetId, $sourceType, $sourceId);

            return;
        }

        $wrongAnswerQuestionReports = $this->getAnswerQuestionReportService()->search([
            'answer_record_id' => $answerRecord['id'],
            'statues' => ['wrong', 'no_answer'],
        ], [], 0, PHP_INT_MAX);

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
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ]);
    }

    protected function dealWrongQuestionExerciseQuestions($answerRecord, $targetType, $targetId, $sourceType, $sourceId)
    {
        $pool = $this->getWrongQuestionService()->getPoolBySceneId($answerRecord['answer_scene_id']);
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
        if ($pool) {
            $this->getWrongQuestionService()->batchBuildCorrectQuestion($correctQuestions, [
                'user_id' => $answerRecord['user_id'],
                'answer_scene_id' => $answerRecord['answer_scene_id'],
                'testpaper_id' => $this->getTestPaperId($answerRecord),
                'target_type' => $pool['target_type'],
                'target_id' => $pool['target_id'],
            ]);
        }

        //===== wrong=====
        $wrongAnswerQuestionReports = $this->getAnswerQuestionReportService()->search([
            'answer_record_id' => $answerRecord['id'],
            'statues' => ['wrong', 'no_answer'],
        ], [], 0, PHP_INT_MAX);

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
            'target_type' => $pool['target_type'],
            'target_id' => $pool['target_id'],
            'source_type' => $sourceType,
            'source_id' => $pool['id'],
        ]);
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
            if ($courseSet['parentId'] > 0) {
                $classCourse = $this->getClassroomService()->getClassroomCourseByCourseSetId($courseSet['id']);
                $targetType = 'classroom';
                $targetId = $classCourse['classroomId'];
            } else {
                $targetType = 'course';
                $targetId = $activity['fromCourseSetId'];
            }
            $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
            $sourceType = 'course_task';
            $sourceId = $task['id'];
        } elseif ($assessmentExerciseRecord = $this->getExerciseModuleService()->getByAnswerSceneId($answerRecord['answer_scene_id'])) {
            $bankExercise = $this->getItemBankExerciseService()->get($assessmentExerciseRecord['exerciseId']);
            $targetType = 'exercise';
            $targetId = empty($bankExercise) ? 0 : $bankExercise['questionBankId'];
            $sourceType = "item_bank_{$assessmentExerciseRecord['type']}"; // chapter|assessment
            $sourceId = $bankExercise['id'];
        } else {
            $targetType = 'wrong_book_exercise';
            $targetId = 0;
            $sourceType = 'wrong_question_exercise';
            $sourceId = 0;
        }

        return [
             $targetType, $targetId, $sourceType, $sourceId,
        ];
    }

    /**
     * @return WrongQuestionDao
     */
    protected function getWrongQuestionDao()
    {
        return $this->getBiz()->dao('WrongBook:WrongQuestionDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
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

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
