<?php
namespace Topxia\Service\Course;

interface MaterialService
{
	public function uploadMaterial($material);

	public function createMaterial($fields);

	public function deleteMaterial($courseId, $materialId);

	public function deleteMaterialByMaterialId($materialId);

	public function deleteMaterialsByLessonId($lessonId);

	public function deleteMaterialsByCourseId($courseId);

	public function getMaterial($courseId, $materialId);

	public function findCourseMaterials($courseId, $start, $limit);

	public function findLessonMaterials($lessonId, $start, $limit);

	public function findMaterialsByPIdAndLockedCourseIds($pId, $courseIds);

	public function getMaterialCount($courseId);

	public function getMaterialCountByFileId($fileId);
}