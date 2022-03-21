<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThreadPostDao extends GeneralDaoInterface
{
    public function searchByUserIdGroupByThreadId($userId, $start, $limit);

    public function countGroupByThreadId($conditions);

    public function deleteByThreadId($threadId);

    public function deleteByCourseId($courseId);

    public function deleteByUserId($userId);

    public function findThreadIds($conditions);
}
