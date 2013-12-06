<?php

namespace Topxia\Service\Quiz\Dao;

interface QuizQuestionChoiceDao
{
    public function addQuestionChoice($choice);
    
    public function updateQuestionChoice($id, $fields);

    public function deleteQuestionChoice($id);

    public function getQuestionChoice($id);

    public function getQuestionChoicesByQuesitonId($quesitonId);

    public function findQuestionChoicesByIds(array $ids);

    public function deleteQuestionChoicesByIds(array $ids);


}