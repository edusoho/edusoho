<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThreadDao extends GeneralDaoInterface
{
    public function deleteByCourseId($courseId);

    public function findLatestThreadsByType($type, $start, $limit);

    public function findEliteThreadsByType($type, $status, $start, $limit);

    /**
     * @deprecated  即将废弃，不建议使用
     *
     * @param $courseId
     * @param $orderBy
     * @param $start
     * @param $limit
     *
     * @return mixed
     */
    public function findThreadsByCourseId($courseId, $orderBy, $start, $limit);

    /**
     * @deprecated  即将废弃，不建议使用
     *
     * @param $courseId
     * @param $orderBy
     * @param $start
     * @param $limit
     *
     * @return mixed
     */
    public function findThreadsByCourseIdAndType($courseId, $type, $orderBy, $start, $limit);

    public function findThreadIds($conditions);
}
