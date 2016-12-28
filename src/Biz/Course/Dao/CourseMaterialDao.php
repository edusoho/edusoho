<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseMaterialDao extends GeneralDaoInterface
{
    public function findByCopyIdAndLockedCourseIds($copyId, $courseIds);

    public function deleteByLessonId($lessonId, $courseType);

    public function deleteByCourseId($courseId, $courseType);

    public function deleteByFileId($fileId);

    public function searchDistinctFileIds($conditions, $orderBys, $start, $limit);

    public function countGroupByFileId($conditions);
}
