<?php

namespace Biz\Marker\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Marker\Dao\QuestionMarkerResultDao;
use Biz\Marker\Service\QuestionMarkerResultService;
use Biz\Marker\Service\QuestionMarkerService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class QuestionMarkerResultServiceImpl extends BaseService implements QuestionMarkerResultService
{
    public function getQuestionMarkerResult($id)
    {
        return $this->getQuestionMarkerResultDao()->get($id);
    }

    public function addQuestionMarkerResult($result)
    {
        return $this->getQuestionMarkerResultDao()->create($result);
    }

    public function updateQuestionMarkerResult($id, $result)
    {
        return $this->getQuestionMarkerResultDao()->update($id, $result);
    }

    public function finishQuestionMarker($questionMarkerId, $fields)
    {
        $fields = ArrayToolkit::parts($fields, ['answer', 'userId', 'taskId']);

        $questionMarker = $this->getQuestionMarkerService()->getQuestionMarker($questionMarkerId);
        $item = $this->getItemService()->getItemWithQuestions($questionMarker['questionId']);
        $itemResponse = [
            'item_id' => $item['id'],
            'question_responses' => [[
                'question_id' => empty($item['questions']) ? 0 : array_shift($item['questions'])['id'],
                'response' => $fields['answer'],
            ]],
        ];
        $reviewResult = $this->getItemService()->review([$itemResponse]);

        $fields['status'] = $reviewResult[0]['result'];
        $fields['markerId'] = $questionMarker['markerId'];
        $fields['questionMarkerId'] = $questionMarker['id'];

        $questionMarkerResult = $this->addQuestionMarkerResult($fields);

        $this->dispatchEvent('question_marker.finish', new Event($questionMarkerResult));

        return $questionMarkerResult;
    }

    public function deleteByQuestionMarkerId($questionMarkerId)
    {
        return $this->getQuestionMarkerResultDao()->deleteByQuestionMarkerId($questionMarkerId);
    }

    public function findByUserIdAndMarkerId($userId, $markerId)
    {
        return $this->getQuestionMarkerResultDao()->findByUserIdAndMarkerId($userId, $markerId);
    }

    public function findByUserIdAndQuestionMarkerId($userId, $questionMarkerId)
    {
        return $this->getQuestionMarkerResultDao()->findByUserIdAndQuestionMarkerId($userId, $questionMarkerId);
    }

    public function findByTaskIdAndQuestionMarkerId($taskId, $questionMarkerId)
    {
        return $this->getQuestionMarkerResultDao()->findByTaskIdAndQuestionMarkerId($taskId, $questionMarkerId);
    }

    public function findResultsByIds($resultIds)
    {
        return $this->getQuestionMarkerResultDao()->findByIds($resultIds);
    }

    public function findByUserIdAndMarkerIds($userId, $markerIds)
    {
        return $this->getQuestionMarkerResultDao()->findByUserIdAndMarkerIds($userId, $markerIds);
    }

    /**
     * @return QuestionMarkerResultDao
     */
    protected function getQuestionMarkerResultDao()
    {
        return $this->createDao('Marker:QuestionMarkerResultDao');
    }

    protected function getQuestionConfig($type)
    {
        return $this->biz["question_type.{$type}"];
    }

    /**
     * @return QuestionMarkerService
     */
    protected function getQuestionMarkerService()
    {
        return $this->biz->service('Marker:QuestionMarkerService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->biz->service('ItemBank:Item:ItemService');
    }
}
