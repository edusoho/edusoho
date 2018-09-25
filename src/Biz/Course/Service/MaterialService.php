<?php

namespace Biz\Course\Service;

interface MaterialService
{
    public function uploadMaterial($material);

    public function addMaterial($fields, $argument);

    public function batchCreateMaterials($materials);

    public function updateMaterial($id, $fields, $argument);

    public function deleteMaterial($courseSetId, $materialId);

    public function deleteMaterialsByLessonId($lessonId, $courseType = 'course');

    public function deleteMaterialsByCourseId($courseId, $courseType = 'course');

    public function deleteMaterialsByCourseSetId($courseSetId, $courseType = 'course');

    public function deleteMaterials($courseSetId, $fileIds, $courseType = 'course');

    public function deleteMaterialsByFileId($fileId);

    public function getMaterial($courseId, $materialId);

    public function findMaterialsByCopyIdAndLockedCourseIds($copyId, $courseIds);

    public function findMaterialsByLessonIdAndSource($lessonId, $source);

    public function searchMaterials($conditions, $orderBy, $start, $limit);

    public function countMaterials($conditions);

    public function searchFileIds($conditions, $orderBy, $start, $limit);

    public function searchMaterialCountGroupByFileId($conditions);

    /**
     * for opencourse.
     *
     * @param  $fileIds
     * @param  $courseId
     *
     * @return mixed
     */
    public function findUsedCourseMaterials($fileIds, $courseId);

    /**
     * for courseSet in course2.0.
     *
     * @param  $fileIds
     * @param  $courseSetId
     *
     * @return mixed
     */
    public function findUsedCourseSetMaterials($fileIds, $courseSetId);

    public function findFullFilesAndSort($materials);

    public function findMaterialsByIds($ids);
}
