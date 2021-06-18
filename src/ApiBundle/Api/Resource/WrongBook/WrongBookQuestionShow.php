<?php


namespace ApiBundle\Api\Resource\WrongBook;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\WrongBook\Service\WrongQuestionService;
use Biz\WrongBook\WrongBookException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
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
        $OrderBys = $this->prepareOrderBys($request->query->all());

        $wrongQuestions = $this->getWrongQuestionService()->searchWrongQuestionWithCollect($conditions, $OrderBys, $offset, $limit);
        $wrongQuestions = $this->makeWrongQuestionInfo($wrongQuestions);
        $wrongQuestionCount = $this->getWrongQuestionService()->countWrongQuestion($conditions);
        return $this->makePagingObject($wrongQuestions, $wrongQuestionCount, $offset, $limit);
    }

    protected function makeWrongQuestionInfo($wrongQuestions)
    {
        $itemsWithQuestion = $this->getItemService()->findItemsByIds(ArrayToolkit::column($wrongQuestions,'item_id'),true);
        $questionReports = $this->getAnswerQuestionReportService()->findByIds(ArrayToolkit::column($wrongQuestions,'answer_question_report_id'));
        foreach ($wrongQuestions as &$wrongQuestion) {
            $questions = $itemsWithQuestion[$wrongQuestion['item_id']]['questions'];
            $questions = ArrayToolkit::index($questions,'id');
            $wrongQuestion['material'] = $itemsWithQuestion[$wrongQuestion['item_id']]['material'];
            $wrongQuestion['question'] = $questions[$wrongQuestion['question_id']];
            $wrongQuestion['report'] = $questionReports[$wrongQuestion['answer_question_report_id']];
            $wrongQuestion['source'] = $this->getWrongQuestionSource($wrongQuestion['answer_scene_id']);
        }
        return $wrongQuestions;
    }

    protected function getWrongQuestionSource($answerSceneId)
    {
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerSceneId);
        if (!empty($activity) && in_array($activity['mediaType'], ['testpaper', 'homework', 'exercise'])) {
            $courseSet = $this->getCourseSetService()->getCourseSet($activity['fromCourseSetId']);
            if ($courseSet['isClassroomRef']) {
                $source = [
                    'courseSet' => $courseSet['title'],
                    'type' => $activity['mediaType']
                ];
            }else{
                $course = $this->getCourseService()->getCourse($activity['fromCourseId']);
                $source = [
                    'course' => $course['title'],
                    'type' => $activity['mediaType']
                ];
            }
        }else {
            $exerciseModule = $this->getExerciseModuleService()->get($answerSceneId);
            $source['type'] = $exerciseModule['type'];
        }
        return $source;
    }

    protected function prepareConditions($poolId, $conditions)
    {
        $prepareConditions = [];

        $prepareConditions['pool_id'] = $poolId;

        return $prepareConditions;
    }

    public function prepareOrderBys($orderBys)
    {
        $prepareOrderBys = ['submit_time' => 'DESC'];

        if(!empty($orderBys['wrongTimesSort'])) {
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
}