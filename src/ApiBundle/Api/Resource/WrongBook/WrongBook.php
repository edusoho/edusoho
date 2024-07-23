<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\WrongBook\Service\WrongQuestionService;
use Biz\WrongBook\WrongBookException;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;

class WrongBook extends AbstractResource
{
    public function get(ApiRequest $request, $poolId)
    {
        $pool = $this->getWrongQuestionService()->getPool($poolId);

        if (empty($pool)) {
            throw WrongBookException::WRONG_QUESTION_BOOK_POOL_NOT_EXIST();
        }

        $conditions = $this->prepareConditions($poolId, $request->query->all());

        $wrongQuestionCount = $this->getWrongQuestionService()->countWrongQuestionWithCollect($conditions);

        return ['wrongNumCount' => $wrongQuestionCount];
    }

    protected function prepareConditions($poolId, $conditions)
    {
        $prepareConditions = [];
        $prepareConditions['pool_id'] = $poolId;
        $prepareConditions['status'] = 'wrong';

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

    /**
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return $this->service('WrongBook:WrongQuestionService');
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->service('ItemBank:Item:ItemCategoryService');
    }
}
