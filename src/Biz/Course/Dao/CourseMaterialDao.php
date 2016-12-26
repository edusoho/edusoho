<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseMaterialDao extends  GeneralDaoInterface
{
    public function findMaterialsByCopyIdAndLockedCourseIds($copyId, $courseIds);

    public function deleteMaterialsByLessonId($lessonId, $courseType);

    public function deleteMaterialsByCourseId($courseId, $courseType);

    public function deleteMaterialsByFileId($fileId);

    public function searchMaterialsGroupByFileId($conditions, $orderBy, $start, $limit);

    public function searchMaterialCountGroupByFileId($conditions);
}