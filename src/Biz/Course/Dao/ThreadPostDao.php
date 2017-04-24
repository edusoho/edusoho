<?php

namespace Biz\Course\Dao;

interface ThreadPostDao
{
    public function searchByGroup($conditions, $orderBys, $start, $limit, $groupBy);

    public function countByGroup($conditions, $groupBy);

    public function deleteByThreadId($threadId);

    public function deleteByCourseId($courseId);
}
