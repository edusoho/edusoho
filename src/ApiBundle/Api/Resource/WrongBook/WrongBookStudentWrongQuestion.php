<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Biz\WrongBook\Service\WrongQuestionService;
use Biz\WrongBook\WrongBookException;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class WrongBookStudentWrongQuestion extends AbstractResource
{
    /**
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN,ROLE_TEACHER")
     */
    public function search(ApiRequest $request, $targetId, $targetType)
    {
        $conditions = $request->query->all();
        if (!in_array($targetType, ['course', 'classroom', 'exercise'])) {
            throw WrongBookException::WRONG_QUESTION_TARGET_TYPE_REQUIRE();
        }

        $pool = 'wrong_question.'.$targetType.'_pool';
        $sceneIds = $this->biz[$pool]->prepareSceneIdsByTargetId($targetId, $conditions);
        $conditions = [
            'answer_scene_ids' => $sceneIds,
        ];
        $wrongTimesSort = $request->query->get('wrongTimesSort', '');
        $orderBys['wrongTimes'] = 'ASC' == $wrongTimesSort ? 'ASC' : 'DESC';

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $wrongQuestions = $this->getWrongQuestionService()->searchWrongQuestionsWithDistinctItem($conditions, $orderBys, $offset, $limit);
        $wrongQuestions = $this->makeCourseWrongQuestionInfo($wrongQuestions, $sceneIds);
        $wrongQuestionCount = $this->getWrongQuestionService()->countWrongQuestionsWithDistinctItem($conditions);

        return $this->makePagingObject($wrongQuestions, $wrongQuestionCount, $offset, $limit);
    }

    protected function makeCourseWrongQuestionInfo($wrongQuestions, $sceneIds)
    {
        $itemIds = ArrayToolkit::column($wrongQuestions, 'item_id');
        $items = $this->getItemService()->findItemsByIds($itemIds);
        $wrongQuestionScenes = $this->getWrongQuestionService()->findWrongQuestionBySceneIds($sceneIds);
        $sources = $this->getCourseWrongQuestionSources($wrongQuestionScenes);
        $wrongQuestionInfo = [];
        foreach ($wrongQuestions as $wrongQuestion) {
            $wrongQuestionInfo[] = [
                'itemId' => $wrongQuestion['item_id'],
                'itemTitle' => $items[$wrongQuestion['item_id']]['material'],
                'taskName' => $sources[$wrongQuestion['item_id']]['taskName'],
                'source' => $sources[$wrongQuestion['item_id']]['source'],
                'wrong_times' => $wrongQuestion['wrongTimes'],
            ];
        }

        return $wrongQuestionInfo;
    }

    protected function getCourseWrongQuestionSources($wrongQuestionScenes)
    {
        $sources = [];
        $sceneIds = array_unique(ArrayToolkit::column($wrongQuestionScenes, 'answer_scene_id'));
        $activityScenes = [];
        array_walk($sceneIds, function ($sceneId) use (&$activityScenes) {
            $activityScenes[$sceneId] = $this->getActivityService()->getActivityByAnswerSceneId($sceneId);
        });

        $tempSceneIds = [];
        foreach ($wrongQuestionScenes as $wrongQuestion) {
            $itemId = $wrongQuestion['item_id'];
            $sceneId = $wrongQuestion['answer_scene_id'];
            $activity = $activityScenes[$sceneId];
            $inItemScene = empty($tempSceneIds[$itemId]) ? [] : $tempSceneIds[$itemId];
            if (!empty($activity) && in_array($activity['mediaType'], ['testpaper', 'homework', 'exercise']) && !in_array($sceneId, $inItemScene)) {
                $courseTask = $this->getCourseTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
                $sources[$itemId]['taskName'][] = $courseTask['title'];
                $source = empty($sources[$itemId]['source']) ? [] : $sources[$itemId]['source'];
                if (!in_array($activity['mediaType'], $source)) {
                    $sources[$itemId]['source'][] = $activity['mediaType'];
                }
                $tempSceneIds[$itemId][] = $sceneId;
            }
        }

        return $sources;
    }

    /**
     * @return TaskService
     */
    protected function getCourseTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return $this->service('WrongBook:WrongQuestionService');
    }
}
