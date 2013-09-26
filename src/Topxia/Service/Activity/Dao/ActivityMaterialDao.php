<?php

namespace Topxia\Service\Activity\Dao;

interface ActivityMaterialDao
{

    public function getMaterial($id);

    public function findMaterialsByActivityId($courseId, $start, $limit);

    public function getMaterialCountByActivityId($courseId);

    public function addMaterial($material);

    public function deleteMaterial($id);

}