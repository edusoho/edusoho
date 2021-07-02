<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\Task\Service\TaskService;
use Biz\WrongBook\Service\WrongQuestionService;
use Biz\WrongBook\WrongBookException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class WrongBookQuestionShow extends AbstractResource
{
    public function search(ApiRequest $request, $poolId)
    {
        $pool = $this->getWrongQuestionService()->getPool($poolId);

        if (empty($pool)) {
            throw WrongBookException::WRONG_QUESTION_BOOK_POOL_NOT_EXIST();
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = $this->prepareConditions($poolId, $request->query->all());
        $orderBys = $this->prepareOrderBys($request->query->all());

        $wrongQuestions = $this->getWrongQuestionService()->searchWrongQuestionsWithCollect($conditions, $orderBys, $offset, $limit);
        $wrongQuestions = $this->makeWrongQuestionInfo($wrongQuestions);
        $wrongQuestionCount = $this->getWrongQuestionService()->countWrongQuestionWithCollect($conditions);

        return $this->makePagingObject($wrongQuestions, $wrongQuestionCount, $offset, $limit);
    }

    protected function makeWrongQuestionInfo($wrongQuestions)
    {
        $itemsWithQuestion = $this->getItemService()->findItemsByIds(ArrayToolkit::column($wrongQuestions, 'item_id'), true);
        $questionReports = $this->getAnswerQuestionReportService()->findByIds(ArrayToolkit::column($wrongQuestions, 'answer_question_report_id'));
        $sources = $this->getWrongQuestionSources(array_unique(ArrayToolkit::column($wrongQuestions, 'answer_scene_id')));
        $wrongQuestionInfo = [];
        foreach ($wrongQuestions as $wrongQuestion) {
            $item = $itemsWithQuestion[$wrongQuestion['item_id']];
            $source = $sources[$wrongQuestion['answer_scene_id']];
            $item['submit_time'] = $wrongQuestion['submit_time'];
            $item['wrong_times'] = $wrongQuestion['wrong_times'];
            $item['source'] = $source;
            foreach ($item['questions'] as &$question) {
                $question['report'] = $questionReports[$wrongQuestion['answer_question_report_id']];
                $question['source'] = $source;
            }
            $wrongQuestionInfo[] = $item;
        }

        return $wrongQuestionInfo;
    }

    protected function getWrongQuestionSources($answerSceneIds)
    {
        $sources = [];
        array_walk($answerSceneIds, function ($answerSceneId) use (&$sources) {
            $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerSceneId);
            if (!empty($activity) && in_array($activity['mediaType'], ['testpaper', 'homework', 'exercise'])) {
                $courseSet = $this->getCourseSetService()->getCourseSet($activity['fromCourseSetId']);
                $courseTask = $this->getCourseTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
                if ($courseSet['isClassroomRef']) {
                    $source = [
                        'mainSource' => $courseSet['title'],
                        'secondarySource' => $courseTask['title'],
                    ];
                } else {
                    $course = $this->getCourseService()->getCourse($activity['fromCourseId']);
                    $source = [
                        'mainSource' => $course['title'],
                        'secondarySource' => $courseTask['title'],
                    ];
                }
            } else {
                $exerciseModule = $this->getExerciseModuleService()->getByAnswerSceneId($answerSceneId);
                $source['mainSource'] = $exerciseModule['title'];
            }
            $sources[$answerSceneId] = $source;
        });

        return $sources;
    }

    protected function prepareConditions($poolId, $conditions)
    {
        $prepareConditions = [];
        $prepareConditions['pool_id'] = $poolId;
        $prepareConditions['user_id'] = $this->getCurrentUser()->getId();

        if (!in_array($conditions['targetType'], ['course', 'classroom', 'exercise'])) {
            throw WrongBookException::WRONG_QUESTION_TARGET_TYPE_REQUIRE();
        }

        $pool = 'wrong_question.'.$conditions['targetType'].'_pool';
        $prepareConditions['answer_scene_ids'] = $this->biz[$pool]->prepareSceneIds($poolId, $conditions);

        if ('exercise' === $conditions['targetType'] && 'chapter' === $conditions['exerciseMediaType'] && !empty($conditions['chapterId'])) {
            $childrenIds = $this->getItemCategoryService()->findCategoryChildrenIds($conditions['chapterId']);
            $prepareConditions['testpaper_ids'] = array_merge([$conditions['chapterId']], $childrenIds);
        }
        if ('exercise' === $conditions['targetType'] && 'testpaper' === $conditions['exerciseMediaType'] && !empty($conditions['testpaperId'])) {
            $prepareConditions['testpaper_id'] = $conditions['testpaperId'];
        }

        return $prepareConditions;
    }

    public function prepareOrderBys($orderBys)
    {
        $prepareOrderBys = ['submit_time' => 'DESC'];

        if (!empty($orderBys['wrongTimesSort'])) {
            $prepareOrderBys = 'ASC' == $orderBys['wrongTimesSort'] ? ['wrong_times' => 'ASC'] : ['wrong_times' => 'DESC'];
        }

        return $prepareOrderBys;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->service('ItemBank:Answer:AnswerQuestionReportService');
    }

    /**
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return $this->service('WrongBook:WrongQuestionService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getExerciseModuleService()
    {
        return $this->service('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return TaskService
     */
    protected function getCourseTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->biz->service('ItemBank:Item:ItemCategoryService');
    }
}
