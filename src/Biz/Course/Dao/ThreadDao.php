<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThreadDao extends GeneralDaoInterface
{
    public function deleteByCourseId($courseId);

    public function findLatestThreadsByType($type, $start, $limit);

    public function findEliteThreadsByType($type, $status, $start, $limit);

    public function findThreadsByCourseId($courseId, $orderBy, $start, $limit);

    public function findThreadsByCourseIdAndType($courseId, $type, $orderBy, $start, $limit);
}
