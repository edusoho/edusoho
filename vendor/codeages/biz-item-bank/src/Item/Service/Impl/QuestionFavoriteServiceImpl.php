<?php

namespace Codeages\Biz\ItemBank\Item\Service\Impl;

use Codeages\Biz\ItemBank\BaseService;
use Codeages\Biz\ItemBank\Item\Service\QuestionFavoriteService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Exception\QuestionException;

class QuestionFavoriteServiceImpl extends BaseService implements QuestionFavoriteService
{
    public function create($questionFavorite)
    {
        $questionFavorite = $this->getValidator()->validate($questionFavorite, [
            'question_id' => ['required', 'integer'],
            'target_id' => ['required', 'integer'],
            'target_type' => ['required'],
            'user_id' => ['required', 'integer'],
        ]);

        $question = $this->getQuestionDao()->get($questionFavorite['question_id']);
        if (empty($question)) {
            throw new QuestionException('Quesiton not found', ErrorCode::QUESTION_NOT_FOUND);
        }

        $favorite = $this->search([
            'question_id' => $questionFavorite['question_id'],
            'target_id' => $questionFavorite['target_id'],
            'target_type' => $questionFavorite['target_type'],
            'user_id' => $questionFavorite['user_id'],
        ], [], 0, 1);

        if ($favorite) {
            return current($favorite);
        } else {
            $questionFavorite['item_id'] = $question['item_id'];
            return $this->getQuestionFavoriteDao()->create($questionFavorite);
        }
    }

    public function delete($id)
    {
        return $this->getQuestionFavoriteDao()->delete($id);
    }

    public function deleteByQuestionFavorite($questionFavorite)
    {
        $questionFavorite = $this->getValidator()->validate($questionFavorite, [
            'question_id' => ['required', 'integer'],
            'target_id' => ['required', 'integer'],
            'target_type' => ['required'],
            'user_id' => ['required', 'integer'],
        ]);

        return $this->getQuestionFavoriteDao()->deleteByQuestionFavorite($questionFavorite);
    }

    public function search($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getQuestionFavoriteDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function count($conditions)
    {
        return $this->getQuestionFavoriteDao()->count($conditions);
    }

    protected function getQuestionDao()
    {
        return $this->biz->dao('ItemBank:Item:QuestionDao');
    }

    protected function getQuestionFavoriteDao()
    {
        return $this->biz->dao('ItemBank:Item:QuestionFavoriteDao');
    }
}
