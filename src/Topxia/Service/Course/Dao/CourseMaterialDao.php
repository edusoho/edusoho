<?php

namespace Topxia\Service\Course\Dao;

interface CourseMaterialDao
{
    public function getMaterial($id);

    public function addMaterial($material);

    public function updateMaterial($id, $fields);

    public function findMaterialsByCopyIdAndLockedCourseIds($copyId, $courseIds);

    public function deleteMaterial($id);

    public function deleteMaterialsByLessonId($lessonId, $courseType);

    public function deleteMaterialsByCourseId($courseId, $courseType);

    public function deleteMaterialsByFileId($fileId);

    public function searchMaterials($conditions, $orderBy, $start, $limit);

    public function searchMaterialCount($conditions);

    public function searchMaterialsGroupByFileId($conditions, $orderBy, $start, $limit);

    public function searchMaterialCountGroupByFileId($conditions);
}