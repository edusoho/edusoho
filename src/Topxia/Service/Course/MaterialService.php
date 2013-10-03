<?php
namespace Topxia\Service\Course;

interface MaterialService
{
	public function uploadMaterial($material);

	public function deleteMaterial($courseId, $materialId);

	public function deleteMaterialsByLessonId($lessonId);

	public function deleteMaterialsByCourseId($courseId);

	public function getMaterial($courseId, $materialId);

    public function findCourseMaterials($courseId, $start, $limit);

	public function findLessonMaterials($lessonId, $start, $limit);

	public function getMaterialCount($courseId);
}