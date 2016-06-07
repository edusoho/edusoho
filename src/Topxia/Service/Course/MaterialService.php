<?php
namespace Topxia\Service\Course;

interface MaterialService
{
    public function uploadMaterial($material);

	public function addMaterial($fields, $argument);

	public function updateMaterial($id, $fields, $argument);

	public function deleteMaterial($courseId, $materialId);

    public function deleteMaterialByMaterialId($materialId);

    public function deleteMaterialsByLessonId($lessonId, $courseType);

    public function deleteMaterialsByCourseId($courseId, $courseType);

	public function deleteMaterials($courseId, $fileIds, $courseType);

	public function deleteMaterialsByFileId($fileId);

	public function getMaterial($courseId, $materialId);

    public function findLessonMaterials($lessonId, $start, $limit);

	public function findMaterialsByCopyIdAndLockedCourseIds($copyId, $courseIds);

	public function getMaterialCount($courseId);

	public function getMaterialCountByFileId($fileId);

	public function searchMaterials($conditions, $orderBy, $start, $limit);

    public function searchMaterialCount($conditions);

    public function searchMaterialsGroupByFileId($conditions, $orderBy, $start, $limit);

    public function searchMaterialCountGroupByFileId($conditions);

    public function findUsedCourseMaterials($courseId, $fileIds);

    public function findFullFilesAndSort($materials);
}
