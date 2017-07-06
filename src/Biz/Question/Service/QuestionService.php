<?php

namespace Biz\Question\Service;

interface QuestionService
{
    public function get($id);

    public function create($fields);

    public function batchCreateQuestions($questions);

    public function update($id, $fields);

    public function updateCopyQuestionsSubCount($parentId, $subCount);

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
