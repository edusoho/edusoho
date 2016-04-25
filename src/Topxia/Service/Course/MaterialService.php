<?php
namespace Topxia\Service\Course;

interface MaterialService
{
    public function uploadMaterial($material);

    public function deleteMaterial($courseId, $materialId);

    public function deleteMaterialByMaterialId($materialId);

    public function deleteMaterialsByLessonId($lessonId, $courseType);

    public function deleteMaterialsByCourseId($courseId, $courseType);

    public function getMaterial($courseId, $materialId);

    public function findLessonMaterials($lessonId, $start, $limit);

    public function searchMaterials($conditions, $orderBy, $start, $limit);

    public function searchMaterialCount($conditions);
}
