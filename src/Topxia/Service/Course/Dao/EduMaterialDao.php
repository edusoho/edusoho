<?php

namespace Topxia\Service\Course\Dao;

interface EduMaterialDao
{
	public function getEduMaterial($id);
	
    public function getEduMaterialByGradeIdAndSubjectId($gradeId,$subjectId);
	
	public function addEduMaterial($eduMaterial);

	public function updateEduMaterial($id,$eduMaterial);

	public function deleteEduMaterial($id);

    public function findAllEduMaterials();

}