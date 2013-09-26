<?php
namespace Topxia\Service\Activity;

interface MaterialService
{
	public function uploadMaterial($material);

	public function deleteMaterial($courseId, $materialId);

	public function getMaterial($courseId, $materialId);

    public function findActivityMaterials($courseId, $start, $limit);

	public function getMaterialCount($courseId);
}