<?php
namespace Topxia\Service\Marker\Dao;

interface QuestionMarkerResultDao
{
    public function getQuestionMarkerResult($id);

    public function addQuestionMarkerResult($result);

    public function updateQuestionMarkerResult($id, $fields);

}
