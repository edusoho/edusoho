<?php
namespace Topxia\Service\Marker;

interface QuestionMarkerResultService
{
    public function getQuestionMarkerResult($id);

    public function addQuestionMarkerResult($result);

    public function updateQuestionMarkerResult($id, $result);

    public function deleteByQuestionMarkerId($questionMarkerId);

    public function finishCurrentQuestion($userId, $questionMarkerId);

    public function findByUserIdAndMarkerId($userId, $markerId);

    public function findByUserIdAndQuestionMarkerId($userId, $questionMarkerId);

}
