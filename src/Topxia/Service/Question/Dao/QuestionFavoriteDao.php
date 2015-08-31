<?php

namespace Topxia\Service\Question\Dao;

interface QuestionFavoriteDao
{
    public function getFavorite($id);

	public function addFavorite ($favorite);

	public function getFavoriteByQuestionIdAndTargetAndUserId ($favorite);

	public function deleteFavorite ($favorite); 

    public function findFavoriteQuestionsByUserId ($id, $start, $limit);

    public function findFavoriteQuestionsCountByUserId ($id);

    public function findAllFavoriteQuestionsByUserId ($id);
}