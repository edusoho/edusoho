<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Util\AssetHelper;
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
        $wrongQuestionScenes = $this->getWrongQuestionService()->findWrongQuestionByCollectIds(ArrayToolkit::column($wrongQuestions, 'collect_id'));
        $sceneIds = array_unique(ArrayToolkit::column($wrongQuestionScenes, 'answer_scene_id'));
        $activityScenes = $this->getActivityScenes($sceneIds);
        $sources = $this->getCourseWrongQuestionSources($wrongQuestionScenes, $activityScenes);
        $wrongQuestionInfo = [];
        foreach ($wrongQuestions as $wrongQuestion) {
            if (empty($itemsWithQuestion[$wrongQuestion['item_id']])) {
                continue;
            }
            $item = $itemsWithQuestion[$wrongQuestion['item_id']];
            $source = empty($sources[$wrongQuestion['item_id']]) ? [] : $sources[$wrongQuestion['item_id']];
            $this->handleImgTag($item['material']);
            $item['submit_time'] = $wrongQuestion['last_submit_time'];
            $item['wrong_times'] = $wrongQuestion['wrong_times'];
            $item['sources'] = $source;
            foreach ($item['questions'] as &$question) {
                $question['report'] = $questionReports[$wrongQuestion['answer_question_report_id']];
                $question['sources'] = $source;
                $this->handleImgTag($question['stem']);
                $this->handleImgTag($question['analysis']);
                foreach ($question['response_points'] as &$points) {
                    if (!empty($points['radio'])) {
                        $this->handleImgTag($points['radio']['text']);
                    }

                    if (!empty($points['checkbox'])) {
                        $this->handleImgTag($points['checkbox']['text']);
                    }
                }
            }
            $wrongQuestionInfo[] = $item;
        }

        return $wrongQuestionInfo;
    }

    protected function handleImgTag(&$itemMaterial)
    {
        if (!preg_match('/<img (.*?)>/', $itemMaterial)) {
            return;
        }

        $reg = '/<img (.*?)+src=[\'"](.*?)[\'"]/i';
        preg_match_all($reg, $itemMaterial, $imgUrls);
        $imgs = array_unique($imgUrls[2]);
        foreach ($imgs as $imgUrl) {
            $realUrl = AssetHelper::uriForPath($imgUrl);
            $itemMaterial = str_replace($imgUrl, $realUrl, $itemMaterial);
        }
    }

    protected function getActivityScenes($sceneIds)
    {
        $activityScenes = [];
        array_walk($sceneIds, function ($sceneId) use (&$activityScenes) {
            $activityScenes[$sceneId] = $this->getActivityService()->getActivityByAnswerSceneId($sceneId);
        });

        return $activityScenes;
    }

    protected function getCourseWrongQuestionSources($wrongQuestionScenes, $activityScenes)
    {
        $sources = [];
        foreach ($wrongQuestionScenes as $wrongQuestion) {
            $itemId = $wrongQuestion['item_id'];
            if (empty($sources[$itemId])) {
                $sources[$itemId] = [];
            }
            $sceneId = $wrongQuestion['answer_scene_id'];
            $activity = $activityScenes[$sceneId];
            if ('course_task' === $wrongQuestion['source_type']) {
                $courseTask = $this->getCourseTaskService()->getTask($wrongQuestion['source_id']);
                $courseSet = $this->getCourseSetService()->getCourseSet($activity['fromCourseSetId']);
                if ($courseSet['parentId'] > 0) {
                    $mainSource = $courseSet['title'];
                } elseif ($courseSet['isClassroomRef']) {
                    $course = $this->getCourseService()->getCourse($activity['fromCourseId']);
                    $mainSource = !empty($course['title']) ? $course['title'] : $courseSet['title'];
                } else {
                    $course = $this->getCourseService()->getCourse($activity['fromCourseId']);
                    $mainSource = $course['title'];
                }
                $secondarySource = $courseTask['title'];

                $sourceTitle = empty($mainSource) ? $secondarySource : $mainSource.'-'.$secondarySource;
                if (!in_array($sourceTitle, $sources[$itemId], true) && !empty($sourceTitle)) {
                    $sources[$itemId][] = $sourceTitle;
                }
            } elseif ('item_bank_chapter' === $wrongQuestion['source_type']) {
                if (!in_array('章节练习', $sources[$itemId], true)) {
                    $sources[$itemId][] = '章节练习';
                }
            } elseif ('item_bank_assessment' === $wrongQuestion['source_type']) {
                if (!in_array('试卷练习', $sources[$itemId], true)) {
                    $sources[$itemId][] = '试卷练习';
                }
            } elseif ('wrong_question_exercise' === $wrongQuestion['source_type']) {
                if (!in_array('错题练习', $sources[$itemId], true)) {
                    $sources[$itemId][] = '错题练习';
                }
            }
        }

        return $sources;
    }

    protected function prepareConditions($poolId, $conditions)
    {
        $prepareConditions = [];
        $prepareConditions['pool_id'] = $poolId;
        $prepareConditions['status'] = 'wrong';
//        $prepareConditions['user_id'] = $this->getCurrentUser()->getId();

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

    protected function prepareOrderBys($orderBys)
    {
        $prepareOrderBys = ['has_answer' => 'DESC', 'last_submit_time' => 'DESC'];

        if (!empty($orderBys['wrongTimesSort'])) {
            $prepareOrderBys = 'ASC' == $orderBys['wrongTimesSort'] ? ['wrong_times' => 'ASC'] : ['wrong_times' => 'DESC'];
        }

        return $prepareOrderBys;
    }

    protected function bankExerciseSourceConstant()
    {
        return [
            'chapter' => '章节练习',
            'assessment' => '试卷练习',
        ];
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
        return $this->service('Task:TaskService');
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->service('ItemBank:Item:ItemCategoryService');
    }
}
