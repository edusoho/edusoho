<?php

namespace Biz\Course\Dao;

interface ThreadPostDao
{
    public function searchByUserIdGroupByThreadId($userId, $start, $limit);

    public function countGroupByThreadId($conditions);

    public function deleteByThreadId($threadId);

    public function deleteByCourseId($courseId);

    public function findThreadIds($conditions);
}
