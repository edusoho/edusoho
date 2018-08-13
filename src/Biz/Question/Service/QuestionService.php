<?php

namespace Biz\Question\Service;

use Biz\System\Annotation\Log;

interface QuestionService
{
    public function get($id);

    public function create($fields);

    public function batchCreateQuestions($questions);

    public function update($id, $fields);

    public function updateCopyQuestionsSubCount($parentId, $subCount);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(level="info",module="course",action="delete_question",message="创建内容",targetType="content",format="[{ 'className':'Question:QuestionService','funcName':'get','param':['id']}]")
     */
    public function delete($id);

    public function deleteSubQuestions($parentId);

    public function findQuestionsByIds(array $ids);

    public function findQuestionsByParentId($id);

    public function findQuestionsByCourseSetId($courseSetId);

    public function search($conditions, $sort, $start, $limit);

    public function searchCount($conditions);

    public function waveCount($id, $diffs);

    public function judgeQuestion($question, $answer);

    public function hasEssay($questionIds);

    public function getQuestionCountGroupByTypes($conditions);

    /**
     * question_favorite.
     */
    public function getFavoriteQuestion($favoriteId);

    public function createFavoriteQuestion($fields);

    public function deleteFavoriteQuestion($id);

    public function searchFavoriteQuestions($conditions, $orderBy, $start, $limit);

    public function searchFavoriteCount($conditions);

    public function findUserFavoriteQuestions($userId);

    public function deleteFavoriteByQuestionId($questionId);

    public function batchDeletes($ids);
}
