<?php

namespace Topxia\Service\Course\Dao;

interface CourseMaterialDao
{
    public function getMaterial($id);

    public function addMaterial($material);

    public function deleteMaterial($id);

    public function searchMaterials($conditions, $orderBy, $start, $limit);

    public function searchMaterialCount($conditions);
}
