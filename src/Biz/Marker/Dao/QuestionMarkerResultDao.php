<?php

namespace Biz\Marker\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface QuestionMarkerResultDao extends GeneralDaoInterface
{
    public function deleteByQuestionMarkerId($questionMarkerId);

    public function findByIds($ids);

    public function findByUserIdAndMarkerId($userId, $markerId);

    public function findByUserIdAndQuestionMarkerId($userId, $questionMarkerId);

    public function countDistinctUserIdByQuestionMarkerIdAndTaskId($questionMarkerId, $taskId);

    public function countDistinctUserIdByTaskId($taskId);

    public function findByTaskIdAndQuestionMarkerId($taskId, $questionMarkerId);

    public function findByUserIdAndMarkerIds($userId, $markerIds);
}
