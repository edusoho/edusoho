<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\User\Service\UserService;
use Biz\WrongBook\Service\WrongQuestionService;
use Biz\WrongBook\WrongBookException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class WrongBookWrongQuestionDetail extends AbstractResource
{
    public function search(ApiRequest $request, $targetType, $itemId)
    {
        if (!in_array($targetType, ['course', 'classroom', 'exercise'])) {
            throw WrongBookException::WRONG_QUESTION_TARGET_TYPE_REQUIRE();
        }
        $orderBy = ['submit_time' => 'DESC'];
        $conditions = $this->prepareConditions($request->query->all(), $targetType, $itemId);
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $wrongQuestionsByUser = $this->getWrongQuestionService()->searchWrongQuestionsWithDistinctUserId($conditions, $orderBy, $offset, $limit);
        $wrongQuestionsUserDetail = $this->getWrongQuestionService()->findWrongQuestionsByUserIdsAndItemIdAndSceneIds(ArrayToolkit::column($wrongQuestionsByUser, 'user_id'), $itemId, $conditions['answer_scene_ids']);
        $wrongQuestionsByUser = $this->makeWrongQuestionDetailInfo($wrongQuestionsByUser, $wrongQuestionsUserDetail);
        $questionsCount = $this->getWrongQuestionService()->countWrongQuestionsWithDistinctUserId($conditions);

        $itemInfo = $this->getItemService()->getItemWithQuestions($itemId, true);

        return array_merge(['item' => $itemInfo], $this->makePagingObject($wrongQuestionsByUser, $questionsCount, $offset, $limit));
    }

    protected function makeWrongQuestionDetailInfo($wrongQuestionsByUser, $wrongQuestionsUserDetail)
    {
        $answerQuestionReportIds = array_merge(ArrayToolkit::column($wrongQuestionsByUser, 'answer_question_report_id'),
            ArrayToolkit::column($wrongQuestionsUserDetail, 'answer_question_report_id'));
        $userIds = ArrayToolkit::column($wrongQuestionsByUser, 'user_id');
        $reports = $this->getAnswerQuestionReportService()->findByIds($answerQuestionReportIds);
        $users = $this->getUserService()->findUsersByIds($userIds);
        $detailInfo = [];
        foreach ($wrongQuestionsByUser as $key => $question) {
            $detailInfo[] = $this->generateDetailData($question, $users, $reports);
            $userDetail = [];
            foreach ($wrongQuestionsUserDetail as $detail) {
                if ($question['user_id'] === $detail['user_id']) {
                    $userDetail[] = $this->generateDetailData($detail, $users, $reports);
                }
            }
            $detailInfo[$key]['wrong_record'] = $userDetail;
        }

        return $detailInfo;
    }

    protected function generateDetailData($question, $users, $reports)
    {
        return [
            'id' => $question['id'],
            'user_id' => $question['user_id'],
            'user_name' => $users[$question['user_id']]['nickname'],
            'answer_time' => $question['submit_time'],
            'answer' => $reports[$question['answer_question_report_id']]['response'],
        ];
    }

    protected function prepareConditions($conditions, $targetType, $itemId)
    {
        if (empty($conditions['targetId'])) {
            throw WrongBookException::WRONG_QUESTION_DATA_FIELDS_MISSING();
        }

        $prepareConditions = [];
        $pool = 'wrong_question.'.$targetType.'_pool';
        $prepareConditions['answer_scene_ids'] = $this->biz[$pool]->prepareSceneIdsByTargetId($conditions['targetId'], $conditions);
        $prepareConditions['item_id'] = $itemId;

        return $prepareConditions;
    }

    /**
     * @return WrongQuestionService
     */
    private function getWrongQuestionService()
    {
        return $this->service('WrongBook:WrongQuestionService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->service('ItemBank:Answer:AnswerQuestionReportService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }
}
