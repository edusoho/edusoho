<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface FavoriteDao extends GeneralDaoInterface
{
    public function getByUserIdAndCourseId($userId, $courseId, $type);

    public function searchByUserId($userId, $start, $limit);

    public function getByUserIdAndCourseSetId($userId, $courseSetId, $type='course');

    public function countByUserId($userId);

    public function deleteByCourseId($courseId);
}