<?php
namespace Topxia\Service\Course;

interface MaterialService
{
	public function uploadMaterial($material);

	public function addMaterial($fields, $argument);

	public function updateMaterial($id, $fields, $argument);

	public function deleteMaterial($courseId, $materialId);

	public function deleteMaterialByMaterialId($materialId);

	public function deleteMaterialsByLessonId($lessonId);

	public function deleteMaterialsByCourseId($courseId);

	public function deleteMaterials($courseId, $fileIds);

	public function deleteMaterialsByFileId($fileId);

	public function getMaterial($courseId, $materialId);

	public function findCourseMaterials($courseId, $start, $limit);

	public function findLessonMaterials($lessonId, $start, $limit);

	public function findMaterialsByCopyIdAndLockedCourseIds($pId, $courseIds);

	public function getMaterialCount($courseId);

	public function getMaterialCountByFileId($fileId);

	public function findMaterialsGroupByFileId($courseId, $start, $limit);

    public function findMaterialCountGroupByFileId($courseId);
	
	public function searchMaterials($conditions, $orderBy, $start, $limit);

    public function searchMaterialCount($conditions);

    public function findUsedCourseMaterials($fileIds, $courseId);
}
