<?php

namespace Topxia\Service\Course\Dao;

interface CourseMaterialDao
{

    public function getMaterial($id);

    public function findMaterialsByCourseId($courseId, $start, $limit);

    public function findMaterialsByLessonId($lessonId, $start, $limit);

    public function getMaterialCountByCourseId($courseId);

    public function addMaterial($material);

    public function deleteMaterial($id);

}