<?php

namespace Topxia\Service\Quiz\Dao;

interface QuizQuestionChoiceDao
{
    public function addChoice($choice);
    
    public function updateChoice($id, $fields);

    public function deleteChoice($id);

    public function getChoice($id);

    public function findChoicesByQuestionIds(array $ids);

    public function deleteChoicesByQuestionIds(array $ids);

}